<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use ZipArchive;

class VerifyLocalBackup extends Command
{
    protected $signature = 'backup:verify {archive : Absolute or backup-folder-relative ZIP path}';
    protected $description = 'Check backup ZIP integrity and SHA-256 manifest entries.';

    public function handle(): int
    {
        $path = $this->argument('archive');
        if (! str_contains($path, ':') && ! str_starts_with($path, DIRECTORY_SEPARATOR)) $path = config('backups.destination').DIRECTORY_SEPARATOR.$path;
        $zip = new ZipArchive();
        if (! is_file($path) || $zip->open($path) !== true || $zip->getFromName('manifest.json') === false) {
            $this->error('Berkas cadangan tidak valid atau manifest tidak ada.');
            return self::FAILURE;
        }
        try {
            $manifest = json_decode($zip->getFromName('manifest.json'), true, flags: JSON_THROW_ON_ERROR);
            foreach ($manifest['files'] ?? [] as $entry => $expected) {
                if (str_contains($entry, '..') || str_starts_with($entry, '/') || ! is_string($expected)) throw new \RuntimeException('manifest_invalid');
                $stream = $zip->getStream($entry);
                if (! is_resource($stream)) throw new \RuntimeException('checksum_mismatch');
                $hash = hash_init('sha256');
                while (! feof($stream)) {
                    $chunk = fread($stream, 1024 * 1024);
                    if ($chunk === false) throw new \RuntimeException('checksum_mismatch');
                    hash_update($hash, $chunk);
                }
                fclose($stream);
                if (! hash_equals($expected, hash_final($hash))) throw new \RuntimeException('checksum_mismatch');
            }
            if (! isset($manifest['files']['database.sql'])) throw new \RuntimeException('database_dump_missing');
            $this->info('Backup valid; semua checksum pada manifest cocok.');
            return self::SUCCESS;
        } catch (\Throwable) {
            $this->error('Backup tidak lolos pemeriksaan struktur/checksum.');
            return self::FAILURE;
        } finally {
            $zip->close();
        }
    }
}
