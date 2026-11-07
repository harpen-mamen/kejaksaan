<?php
namespace App\Filament\Resources\AuditLogs;
use App\Models\AuditLog;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
class AuditLogResource extends Resource
{
    protected static ?string $model = AuditLog::class;
    protected static ?string $modelLabel = 'audit log';
    protected static ?string $pluralModelLabel = 'Audit Log';
    protected static string|\UnitEnum|null $navigationGroup = 'Pengawasan';
    public static function canViewAny(): bool { return auth()->user()?->hasPermissionTo('audit.view') ?? false; }
    public static function canCreate(): bool { return false; }
    public static function canEdit($record): bool { return false; }
    public static function canDelete($record): bool { return false; }
    public static function form(Schema $schema): Schema { return $schema->components([]); }
    public static function table(Table $table): Table { return $table->defaultSort('created_at', 'desc')->columns([
        TextColumn::make('created_at')->label('Waktu')->dateTime()->sortable(), TextColumn::make('user.name')->label('Pengguna')->placeholder('Sistem'),
        TextColumn::make('action')->label('Aksi')->searchable()->sortable(), TextColumn::make('status')->label('Status')->badge(),
        TextColumn::make('subject_type')->label('Objek')->toggleable(), TextColumn::make('subject_id')->label('ID objek')->toggleable(),
        TextColumn::make('request_id')->label('Request ID')->copyable()->toggleable(isToggledHiddenByDefault: true),
    ]); }
    public static function getPages(): array { return ['index' => Pages\ListAuditLogs::route('/')]; }
}
