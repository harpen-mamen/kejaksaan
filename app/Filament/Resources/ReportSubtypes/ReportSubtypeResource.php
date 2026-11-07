<?php

namespace App\Filament\Resources\ReportSubtypes;

use App\Models\ReportSubtype;
use App\Models\ReportType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ReportSubtypeResource extends Resource
{
    protected static ?string $model = ReportSubtype::class;
    protected static ?string $modelLabel = 'subjenis laporan';
    protected static ?string $pluralModelLabel = 'Subjenis Laporan';
    protected static ?string $navigationLabel = 'Subjenis Laporan';
    protected static string|\UnitEnum|null $navigationGroup = 'Pengaturan Laporan';
    public static function canViewAny(): bool { return auth()->user()?->hasPermissionTo('settings.manage') ?? false; }
    public static function canCreate(): bool { return static::canViewAny(); }
    public static function canEdit($record): bool { return static::canViewAny(); }
    public static function canDelete($record): bool { return false; }
    public static function canDeleteAny(): bool { return false; }
    public static function form(Schema $schema): Schema { return $schema->components([
        Select::make('report_type_id')->label('Jenis laporan')->options(fn () => ReportType::orderBy('name')->pluck('name', 'id'))->required()->searchable(),
        TextInput::make('name')->label('Nama subjenis')->required()->maxLength(120),
        TextInput::make('code')->label('Kode')->required()->maxLength(40),
        Textarea::make('description')->label('Keterangan')->maxLength(1000),
        Toggle::make('is_active')->label('Aktif')->default(false)->helperText('Aktifkan setelah kategori ini dikonfirmasi sesuai praktik kantor.'),
    ]); }
    public static function table(Table $table): Table { return $table->columns([
        TextColumn::make('reportType.name')->label('Jenis')->sortable(),
        TextColumn::make('name')->label('Subjenis')->searchable()->sortable(),
        TextColumn::make('code')->label('Kode'),
        TextColumn::make('is_active')->label('Aktif')->badge()->formatStateUsing(fn ($state) => $state ? 'Ya' : 'Tidak'),
    ])->recordActions([\Filament\Actions\EditAction::make()]); }
    public static function getPages(): array { return [
        'index' => Pages\ListReportSubtypes::route('/'), 'create' => Pages\CreateReportSubtype::route('/create'), 'edit' => Pages\EditReportSubtype::route('/{record}/edit'),
    ]; }
}
