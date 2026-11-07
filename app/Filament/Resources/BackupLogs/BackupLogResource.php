<?php
namespace App\Filament\Resources\BackupLogs;
use App\Models\BackupLog;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
class BackupLogResource extends Resource
{
    protected static ?string $model = BackupLog::class;
    protected static ?string $modelLabel = 'log backup';
    protected static ?string $pluralModelLabel = 'Riwayat Backup';
    protected static string|\UnitEnum|null $navigationGroup = 'Pengawasan';
    public static function canViewAny(): bool { return auth()->user()?->hasPermissionTo('backup.manage') ?? false; }
    public static function canCreate(): bool { return false; }
    public static function canEdit($record): bool { return false; }
    public static function canDelete($record): bool { return false; }
    public static function form(Schema $schema): Schema { return $schema->components([]); }
    public static function table(Table $table): Table { return $table->defaultSort('created_at', 'desc')->columns([
        TextColumn::make('created_at')->label('Waktu')->dateTime()->sortable(), TextColumn::make('user.name')->label('Pemrakarsa')->placeholder('Scheduler/CLI'),
        TextColumn::make('archive_name')->label('Nama berkas cadangan')->searchable(), TextColumn::make('size_bytes')->label('Ukuran')->numeric(),
        TextColumn::make('sha256')->label('SHA-256')->copyable()->limit(20), TextColumn::make('status')->label('Status')->badge(), TextColumn::make('failure_code')->label('Kode kegagalan'),
    ])->recordActions([
        \Filament\Actions\Action::make('verify')->label('Verifikasi checksum')->visible(fn (BackupLog $record) => $record->status === 'success' && filled($record->archive_name))
            ->action(function (BackupLog $record): void {
                $output = new \Symfony\Component\Console\Output\BufferedOutput();
                $result = \Illuminate\Support\Facades\Artisan::call('backup:verify', ['archive' => $record->archive_name], $output);
                $notice = \Filament\Notifications\Notification::make()->title($result === 0 ? 'Backup valid' : 'Verifikasi gagal');
                if ($result === 0) $notice->success()->body(trim($output->fetch()));
                else $notice->danger()->body('Checksum atau struktur backup tidak lolos pemeriksaan.');
                $notice->send();
            }),
    ]); }
    public static function getPages(): array { return ['index' => Pages\ListBackupLogs::route('/')]; }
}
