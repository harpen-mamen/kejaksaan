<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\ActivityCategory;
use App\Models\ArchiveDocument;
use App\Models\ReportSubtype;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\WorkOrderType;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        $actor = User::where('is_active', true)->whereHas('roles', fn ($q) => $q->where('slug', 'administrator-sistem'))->firstOrFail();
        $this->call([ActivityCategorySeeder::class, ReportTypeSeeder::class, WorkOrderSetupSeeder::class]);
        $today = CarbonImmutable::today('Asia/Singapore');
        $categories = ActivityCategory::where('is_active', true)->orderBy('id')->get();
        $subtypes = ReportSubtype::where('is_active', true)->whereHas('reportType', fn ($q) => $q->where('is_active', true))->orderBy('id')->get();
        $types = WorkOrderType::where('is_active', true)->orderBy('id')->get();
        if ($categories->isEmpty() || $subtypes->isEmpty() || $types->isEmpty()) {
            throw new RuntimeException('Master data aktif belum lengkap.');
        }
        $titles = ['Rapat koordinasi administrasi', 'Penyusunan laporan bulanan', 'Evaluasi pengelolaan arsip', 'Pendataan dokumen kegiatan', 'Rekapitulasi kegiatan unit', 'Sosialisasi tata kelola laporan'];
        $statuses = array_keys(Activity::REPORT_STATUSES);
        $verification = array_keys(ArchiveDocument::VERIFICATION_STATUSES);
        $disk = Storage::disk('archive_private');
        for ($i = 1; $i <= 24; $i++) {
            $date = $today->subDays(($i - 1) * 5);
            $status = $statuses[($i - 1) % count($statuses)];
            $activity = Activity::withTrashed()->firstOrCreate(['reference_code' => sprintf('DUMMY-KEG-%03d', $i)], [
                'title' => '[DUMMY] '.$titles[($i - 1) % count($titles)].' '.$i,
                'activity_category_id' => $categories[($i - 1) % $categories->count()]->id,
                'activity_date' => $date->toDateString(), 'activity_year' => $date->year,
                'unit' => 'Unit Contoh '.(($i - 1) % 3 + 1), 'report_status' => $status,
                'report_submitted_at' => $status === 'belum_dikirim' ? null : $date->toDateString(),
                'administrative_notes' => 'DUMMY: data fiktif untuk latihan penggunaan aplikasi.',
                'created_by' => $actor->id, 'updated_by' => $actor->id,
            ]);
            $code = sprintf('DUMMY-LAP-%03d', $i);
            if ($i > 18 || ArchiveDocument::withTrashed()->where('archive_code', $code)->exists()) {
                continue;
            }
            $subtype = $subtypes[($i - 1) % $subtypes->count()];
            $content = "LAPORAN CONTOH - DUMMY\nKode: {$code}\nTanggal: {$date->toDateString()}\nKegiatan: {$activity->title}\n\nSeluruh isi dokumen ini fiktif untuk pengujian aplikasi.\nRingkasan: Koordinasi dan rekapitulasi administrasi unit contoh.\nHasil: Dokumen contoh telah disusun untuk simulasi pelaporan.\n";
            $path = 'documents/'.Str::uuid().'.txt';
            if (! $disk->put($path, $content)) {
                throw new RuntimeException('Gagal menyimpan berkas dummy.');
            }
            try {
                DB::transaction(function () use ($code, $subtype, $activity, $date, $status, $actor, $content, $path, $verification, $i) {
                    $file = ['storage_path' => $path, 'original_file_name' => strtolower($code).'.txt', 'mime_type' => 'text/plain', 'file_size' => strlen($content), 'sha256' => hash('sha256', $content), 'uploaded_by' => $actor->id, 'uploaded_at' => now()];
                    $document = ArchiveDocument::create([
                        'archive_code' => $code, 'report_type_id' => $subtype->report_type_id, 'report_subtype_id' => $subtype->id,
                        'activity_id' => $activity->id, 'title' => '[DUMMY] Laporan '.$activity->title,
                        'document_number' => sprintf('DUMMY/%03d/%s', $i, $date->year), 'report_date' => $date->toDateString(), 'report_year' => $date->year,
                        ...$file, 'sending_status' => $status, 'sent_to_kejati_at' => $status === 'belum_dikirim' ? null : $date->toDateString(),
                        'verification_status' => $verification[($i - 1) % count($verification)],
                        'administrative_notes' => 'DUMMY: laporan dan lampiran fiktif untuk latihan.', 'created_by' => $actor->id, 'updated_by' => $actor->id,
                    ]);
                    $document->versions()->create(['version_number' => 1, ...$file]);
                });
            } catch (\Throwable $exception) {
                $disk->delete($path);
                throw $exception;
            }
        }
        $offsets = [-15, -7, -1, 0, 1, 3, 7, 14, 21, 30, -10, -3];
        foreach ($offsets as $index => $offset) {
            $end = $today->addDays($offset);
            $type = $types[$index % $types->count()];
            $duration = (int) $type->default_duration_days;
            $closed = $index >= 10;
            $order = WorkOrder::firstOrCreate(['number' => sprintf('DUMMY-SP-%03d', $index + 1)], [
                'work_order_type_id' => $type->id, 'start_date' => $end->subDays($duration)->toDateString(),
                'duration_days' => $duration, 'duration_basis' => 'calendar_days', 'start_date_is_day_one' => false,
                'end_date' => $end->toDateString(), 'status' => $closed ? 'closed' : 'active',
                'responsible_user_id' => $actor->id, 'closed_at' => $closed ? $end->toDateString() : null,
                'administrative_notes' => 'DUMMY: simulasi surat perintah administratif.', 'created_by' => $actor->id, 'updated_by' => $actor->id,
            ]);
            if ($order->wasRecentlyCreated) {
                $order->events()->create(['user_id' => $actor->id, 'event_type' => 'created', 'new_end_date' => $order->end_date, 'new_duration_days' => $duration, 'reason' => 'DUMMY: pembuatan data contoh.']);
                if ($closed) {
                    $order->events()->create(['user_id' => $actor->id, 'event_type' => 'closed', 'reason' => 'DUMMY: simulasi penutupan surat.']);
                }
            }
        }
        // Verify persisted records and downloadable attachments, including on repeat runs.
        $documents = ArchiveDocument::where('archive_code', 'like', 'DUMMY-LAP-%')->get();
        foreach ($documents as $document) {
            if (! $disk->exists($document->storage_path) || hash('sha256', $disk->get($document->storage_path)) !== $document->sha256 || ! $document->versions()->exists()) {
                throw new RuntimeException('Verifikasi berkas dummy gagal: '.$document->archive_code);
            }
        }
        $this->command?->info(sprintf('Data DUMMY tersedia: %d kegiatan, %d laporan beserta berkas, %d surat perintah.', Activity::where('reference_code', 'like', 'DUMMY-KEG-%')->count(), $documents->count(), WorkOrder::where('number', 'like', 'DUMMY-SP-%')->count()));
    }
}
