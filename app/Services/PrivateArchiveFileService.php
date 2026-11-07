<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class PrivateArchiveFileService
{
    /** @return array{storage_path: string, original_file_name: string, mime_type: string, file_size: int, sha256: string} */
    public function store(UploadedFile $file): array
    {
        $path = $file->getRealPath();
        if (! $path || ! is_file($path) || ! is_readable($path)) {
            throw ValidationException::withMessages(['upload' => 'Berkas tidak dapat dibaca.']);
        }

        $size = filesize($path);
        if ($size === false || $size < 1 || $size > config('archives.max_kb') * 1024) {
            throw ValidationException::withMessages(['upload' => 'Ukuran berkas tidak diizinkan.']);
        }

        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($path) ?: '';
        $extension = strtolower($file->getClientOriginalExtension());
        $allowedExtensions = config("archives.extensions_by_mime.{$mime}", []);

        if (! in_array($mime, config('archives.mime_types', []), true) || ! in_array($extension, $allowedExtensions, true)) {
            throw ValidationException::withMessages(['upload' => 'Jenis atau isi berkas tidak termasuk format yang diizinkan.']);
        }

        $storagePath = 'documents/'.Str::uuid().'.'.$extension;
        $storedPath = $file->storeAs('documents', basename($storagePath), 'archive_private');
        if (! $storedPath) {
            throw new RuntimeException('Penyimpanan berkas privat gagal.');
        }

        $storedFilePath = Storage::disk('archive_private')->path($storedPath);
        $checksum = hash_file('sha256', $storedFilePath);
        if ($checksum === false) {
            Storage::disk('archive_private')->delete($storedPath);
            throw new RuntimeException('Checksum berkas tidak dapat dihitung.');
        }

        return [
            'storage_path' => $storedPath,
            'original_file_name' => mb_substr(basename(str_replace('\\', '/', $file->getClientOriginalName())), 0, 255),
            'mime_type' => $mime,
            'file_size' => (int) $size,
            'sha256' => $checksum,
        ];
    }

    public function delete(string $path): void
    {
        if (preg_match('#^documents/[0-9a-f-]{36}\.(pdf|txt|docx|xlsx|jpg|jpeg|png)$#i', $path)) {
            Storage::disk('archive_private')->delete($path);
        }
    }
}
