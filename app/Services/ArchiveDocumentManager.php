<?php

namespace App\Services;

use App\Models\ArchiveDocument;
use App\Models\ArchiveDocumentVersion;
use App\Models\ReportSubtype;
use App\Models\ReportType;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use Throwable;

class ArchiveDocumentManager
{
    public function __construct(
        private readonly PrivateArchiveFileService $files,
        private readonly AuditLogService $audit,
        private readonly DatabaseManager $database,
    ) {}

    public function create(array $data, UploadedFile $upload, User $actor): ArchiveDocument
    {
        $this->assertReportClassification((int) $data['report_type_id'], isset($data['report_subtype_id']) ? (int) $data['report_subtype_id'] : null);
        $stored = $this->files->store($upload);

        try {
            return $this->database->transaction(function () use ($data, $actor, $stored): ArchiveDocument {
                $reportDate = CarbonImmutable::parse($data['report_date'], config('app.timezone'));
                $document = ArchiveDocument::query()->create([
                    ...$data,
                    'report_year' => $reportDate->year,
                    ...$stored,
                    'uploaded_by' => $actor->getKey(),
                    'uploaded_at' => now(),
                    'created_by' => $actor->getKey(),
                    'updated_by' => $actor->getKey(),
                ]);

                $document->versions()->create([
                    'version_number' => 1,
                    ...$stored,
                    'uploaded_by' => $actor->getKey(),
                    'uploaded_at' => now(),
                ]);

                $this->audit->record('archive.uploaded', $actor, $document, context: ['version' => 1]);

                return $document;
            });
        } catch (Throwable $exception) {
            $this->files->delete($stored['storage_path']);
            throw $exception;
        }
    }

    public function update(ArchiveDocument $document, array $data, ?UploadedFile $upload, User $actor): ArchiveDocument
    {
        $this->assertReportClassification(
            (int) $data['report_type_id'],
            isset($data['report_subtype_id']) ? (int) $data['report_subtype_id'] : null,
            $document,
        );
        $stored = $upload ? $this->files->store($upload) : null;

        try {
            return $this->database->transaction(function () use ($document, $data, $actor, $stored): ArchiveDocument {
                $document = ArchiveDocument::query()->lockForUpdate()->findOrFail($document->getKey());
                $reportDate = CarbonImmutable::parse($data['report_date'], config('app.timezone'));
                $document->fill([
                    ...$data,
                    'report_year' => $reportDate->year,
                    'updated_by' => $actor->getKey(),
                ])->save();

                if ($stored) {
                    $versionNumber = ((int) $document->versions()->max('version_number')) + 1;
                    $document->fill([
                        ...$stored,
                        'uploaded_by' => $actor->getKey(),
                        'uploaded_at' => now(),
                    ])->save();
                    $document->versions()->create([
                        'version_number' => $versionNumber,
                        ...$stored,
                        'uploaded_by' => $actor->getKey(),
                        'uploaded_at' => now(),
                    ]);
                    $this->audit->record('archive.version_uploaded', $actor, $document, context: ['version' => $versionNumber]);
                } else {
                    $this->audit->record('archive.updated', $actor, $document);
                }

                return $document->refresh();
            });
        } catch (Throwable $exception) {
            if ($stored) {
                $this->files->delete($stored['storage_path']);
            }
            throw $exception;
        }
    }

    private function assertReportClassification(int $reportTypeId, ?int $reportSubtypeId, ?ArchiveDocument $existing = null): void
    {
        $typeIsUnchanged = $existing && (int) $existing->report_type_id === $reportTypeId;
        if (! ReportType::query()->whereKey($reportTypeId)->when(! $typeIsUnchanged, fn ($query) => $query->where('is_active', true))->exists()) {
            throw ValidationException::withMessages(['report_type_id' => 'Jenis laporan tidak tersedia atau tidak aktif.']);
        }

        if ($reportSubtypeId === null) {
            return;
        }

        $subtypeIsUnchanged = $existing && (int) $existing->report_subtype_id === $reportSubtypeId && $typeIsUnchanged;
        if (! ReportSubtype::query()->whereKey($reportSubtypeId)->where('report_type_id', $reportTypeId)->when(! $subtypeIsUnchanged, fn ($query) => $query->where('is_active', true))->exists()) {
            throw ValidationException::withMessages(['report_subtype_id' => 'Subjenis laporan tidak sesuai dengan jenis laporan yang dipilih.']);
        }
    }
}
