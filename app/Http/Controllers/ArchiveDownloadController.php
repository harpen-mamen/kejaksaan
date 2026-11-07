<?php

namespace App\Http\Controllers;

use App\Models\ArchiveDocument;
use App\Models\ArchiveDocumentVersion;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ArchiveDownloadController extends Controller
{
    public function current(Request $request, ArchiveDocument $archiveDocument, AuditLogService $audit): StreamedResponse
    {
        Gate::authorize('download', $archiveDocument);

        return $this->stream(
            $request,
            $archiveDocument,
            $archiveDocument->storage_path,
            $archiveDocument->original_file_name,
            $archiveDocument->mime_type,
            null,
            $audit,
        );
    }

    public function version(
        Request $request,
        ArchiveDocument $archiveDocument,
        ArchiveDocumentVersion $version,
        AuditLogService $audit,
    ): StreamedResponse {
        Gate::authorize('download', $archiveDocument);
        abort_unless($version->archive_document_id === $archiveDocument->id, 404);

        return $this->stream(
            $request,
            $archiveDocument,
            $version->storage_path,
            $version->original_file_name,
            $version->mime_type,
            $version->version_number,
            $audit,
        );
    }

    private function stream(
        Request $request,
        ArchiveDocument $document,
        string $path,
        string $originalName,
        string $mime,
        ?int $version,
        AuditLogService $audit,
    ): StreamedResponse {
        abort_unless($request->user()?->is_active, 404);
        abort_unless(preg_match('#^documents/[0-9a-f-]{36}\.(pdf|txt|docx|xlsx|jpg|jpeg|png)$#i', $path), 404);

        $disk = Storage::disk('archive_private');
        abort_unless($disk->exists($path), 404);
        $stream = $disk->readStream($path);
        abort_unless(is_resource($stream), 404);

        try {
            $audit->record('archive.downloaded', $request->user(), $document, context: ['version' => $version]);
        } catch (\Throwable $exception) {
            fclose($stream);
            throw $exception;
        }

        $downloadName = strtr(basename(str_replace('\\', '/', $originalName)), ["\r" => '_', "\n" => '_', '"' => '_', '/' => '_', '\\' => '_']) ?: 'laporan';

        return response()->streamDownload(
            static function () use ($stream): void {
                fpassthru($stream);
                fclose($stream);
            },
            $downloadName,
            [
                'Content-Type' => $mime,
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control' => 'private, no-store, max-age=0',
                'Pragma' => 'no-cache',
            ],
        );
    }
}
