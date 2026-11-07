<?php

namespace App\Console\Commands;

use App\Models\BackupLog;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use ZipArchive;

class CreateLocalBackup extends Command
{
    protected $signature = 'backup:create';
    protected $description = 'Create a local database and private-archive backup bundle.';

    public function handle(AuditLogService $audit): int
    {
        $user = auth()->user();
        $name = 'siak-intel-'.now(config('app.timezone'))->format('Ymd-His').'.zip';
        $destination = rtrim(config('backups.destination'), '\\/');
        $zipPath = $destination.DIRECTORY_SEPARATOR.$name;
        $log = BackupLog::query()->create(['user_id' => $user?->getKey(), 'archive_name' => $name, 'status' => 'running', 'created_at' => now()]);
        $tempDir = storage_path('app/backup-tmp/'.\Illuminate\Support\Str::uuid());
        $optionFile = $tempDir.DIRECTORY_SEPARATOR.'mysql.cnf';
        $dumpPath = $tempDir.DIRECTORY_SEPARATOR.'database.sql';
        $zip = null;
        $createdArchive = false;

        try {
            if (! class_exists(ZipArchive::class)) {
                throw new \RuntimeException('zip_extension_missing');
            }
            if (! is_dir($destination) && ! mkdir($destination, 0700, true) && ! is_dir($destination)) {
                throw new \RuntimeException('backup_destination_unavailable');
            }
            if (! is_dir($tempDir) && ! mkdir($tempDir, 0700, true) && ! is_dir($tempDir)) {
                throw new \RuntimeException('backup_temp_unavailable');
            }

            $database = config('database.connections.mysql');
            $optionText = "[client]\n";
            foreach (['host' => $database['host'], 'port' => $database['port'], 'user' => $database['username'], 'password' => $database['password'], 'database' => $database['database']] as $key => $value) {
                $escaped = str_replace(["\\", '"', "\n", "\r"], ["\\\\", '\\"', '', ''], (string) $value);
                $optionText .= $key.'="'.$escaped."\"\n";
            }
            if (file_put_contents($optionFile, $optionText, LOCK_EX) === false) {
                throw new \RuntimeException('backup_credentials_file_unavailable');
            }
            @chmod($optionFile, 0600);

            $process = new Process([config('backups.mysqldump'), '--defaults-extra-file='.$optionFile, '--single-transaction', '--quick', '--routines', '--triggers', '--result-file='.$dumpPath]);
            $process->setTimeout(1800);
            $process->run();
            if (! $process->isSuccessful() || ! is_file($dumpPath) || filesize($dumpPath) < 1) {
                throw new \RuntimeException('database_dump_failed');
            }

            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::EXCL) !== true) {
                throw new \RuntimeException('backup_archive_create_failed');
            }
            $createdArchive = true;
            if (! $zip->addFile($dumpPath, 'database.sql')) {
                throw new \RuntimeException('backup_database_add_failed');
            }

            $archiveRoot = storage_path('app/private/archives');
            $manifest = ['created_at' => now(config('app.timezone'))->toIso8601String(), 'database' => config('database.connections.mysql.database'), 'files' => []];
            if (is_dir($archiveRoot)) {
                $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($archiveRoot, \FilesystemIterator::SKIP_DOTS));
                foreach ($iterator as $file) {
                    if (! $file->isFile() || $file->isLink()) continue;
                    $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($archiveRoot) + 1));
                    $entry = 'private-archives/'.$relative;
                    if (! $zip->addFile($file->getPathname(), $entry)) throw new \RuntimeException('backup_file_add_failed');
                    $manifest['files'][$entry] = hash_file('sha256', $file->getPathname());
                }
            }
            $manifest['files']['database.sql'] = hash_file('sha256', $dumpPath);
            if (! $zip->addFromString('manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR))) {
                throw new \RuntimeException('backup_manifest_add_failed');
            }
            if (! $zip->close()) throw new \RuntimeException('backup_archive_close_failed');
            $zip = null;

            $size = filesize($zipPath) ?: 0;
            $sha = hash_file('sha256', $zipPath);
            $log->update(['sha256' => $sha, 'size_bytes' => $size, 'status' => 'success']);
            $audit->record('backup.created', $user, $log, context: ['archive_name' => $name, 'size_bytes' => $size]);
            $this->info("Backup selesai: {$name} ({$size} bytes).");
            return self::SUCCESS;
        } catch (\Throwable $exception) {
            if ($zip instanceof ZipArchive) $zip->close();
            if ($createdArchive && is_file($zipPath)) @unlink($zipPath);
            $code = preg_match('/^[a-z_]+$/', $exception->getMessage()) ? $exception->getMessage() : 'backup_failed';
            $log->update(['status' => 'failed', 'failure_code' => $code]);
            $audit->record('backup.failed', $user, $log, status: 'failed', context: ['failure_code' => $code]);
            report($exception);
            $this->error("Backup gagal ({$code}). Rincian hanya dicatat pada log aplikasi.");
            return self::FAILURE;
        } finally {
            @unlink($optionFile);
            if (is_dir($tempDir)) {
                foreach (glob($tempDir.DIRECTORY_SEPARATOR.'*') ?: [] as $file) @unlink($file);
                @rmdir($tempDir);
            }
        }
    }
}
