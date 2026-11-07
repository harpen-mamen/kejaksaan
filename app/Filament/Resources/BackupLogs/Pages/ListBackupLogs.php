<?php
namespace App\Filament\Resources\BackupLogs\Pages;
use App\Filament\Resources\BackupLogs\BackupLogResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Output\BufferedOutput;
class ListBackupLogs extends ListRecords
{
    protected static string $resource = BackupLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('createBackup')->label('Buat backup lokal')->icon('heroicon-o-archive-box')->requiresConfirmation()
                ->action(function (): void {
                    $output = new BufferedOutput();
                    $result = Artisan::call('backup:create', [], $output);
                    $notice = Notification::make()->title($result === 0 ? 'Backup selesai' : 'Backup gagal');
                    if ($result === 0) $notice->success()->body(trim($output->fetch()));
                    else $notice->danger()->body('Periksa log aplikasi dan kode kegagalan pada riwayat backup.');
                    $notice->send();
                }),
        ];
    }
}
