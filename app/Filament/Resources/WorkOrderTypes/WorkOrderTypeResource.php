<?php

namespace App\Filament\Resources\WorkOrderTypes;

use App\Models\WorkOrderType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WorkOrderTypeResource extends Resource
{
    protected static ?string $model = WorkOrderType::class;
    protected static ?string $modelLabel = 'jenis surat perintah';
    protected static ?string $pluralModelLabel = 'Konfigurasi Jenis Surat';
    protected static string|\UnitEnum|null $navigationGroup = 'Pengaturan Tenggat';
    public static function canViewAny(): bool { return auth()->user()?->hasPermissionTo('settings.manage') ?? false; }
    public static function canCreate(): bool { return static::canViewAny(); }
    public static function canEdit($record): bool { return static::canViewAny(); }
    public static function canDelete($record): bool { return false; }
    public static function canDeleteAny(): bool { return false; }
    public static function form(Schema $schema): Schema { return $schema->components([
        TextInput::make('name')->label('Nama')->required()->maxLength(120),
        TextInput::make('code')->label('Kode')->required()->maxLength(50)->alphaDash()->unique(ignoreRecord: true),
        TextInput::make('default_duration_days')->label('Durasi awal')->numeric()->minValue(1)->maxValue(3650)->required(),
        Select::make('duration_basis')->label('Dasar masa berlaku')->options(['calendar_days' => 'Hari kalender', 'working_days' => 'Hari kerja'])->required(),
        Toggle::make('start_date_is_day_one')->label('Tanggal mulai dihitung sebagai hari pertama')->default(false),
        Textarea::make('calculation_note')->label('Catatan aturan dan persetujuan')->required()->maxLength(1000),
        Toggle::make('is_active')->label('Aktif')->default(true),
    ]); }
    public static function table(Table $table): Table { return $table->columns([
        TextColumn::make('name')->label('Jenis surat')->searchable()->sortable(), TextColumn::make('default_duration_days')->label('Durasi'),
        TextColumn::make('duration_basis')->label('Dasar'), TextColumn::make('start_date_is_day_one')->label('Mulai = hari 1')->formatStateUsing(fn ($state) => $state ? 'Ya' : 'Tidak'),
        TextColumn::make('calculation_note')->label('Catatan aturan')->limit(70), TextColumn::make('is_active')->label('Aktif')->badge()->formatStateUsing(fn ($state) => $state ? 'Ya' : 'Tidak'),
    ])->recordActions([\Filament\Actions\EditAction::make()]); }
    public static function getPages(): array { return ['index' => Pages\ListWorkOrderTypes::route('/'), 'create' => Pages\CreateWorkOrderType::route('/create'), 'edit' => Pages\EditWorkOrderType::route('/{record}/edit')]; }
}
