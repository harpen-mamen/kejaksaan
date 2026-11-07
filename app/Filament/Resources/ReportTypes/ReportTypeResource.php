<?php

namespace App\Filament\Resources\ReportTypes;

use App\Filament\Resources\ReportTypes\Pages\CreateReportType;
use App\Filament\Resources\ReportTypes\Pages\EditReportType;
use App\Filament\Resources\ReportTypes\Pages\ListReportTypes;
use App\Filament\Resources\ReportTypes\Schemas\ReportTypeForm;
use App\Filament\Resources\ReportTypes\Tables\ReportTypesTable;
use App\Models\ReportType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ReportTypeResource extends Resource
{
    protected static ?string $modelLabel = 'jenis laporan';
    protected static ?string $pluralModelLabel = 'Jenis Laporan';
    protected static ?string $navigationLabel = 'Jenis Laporan';
    protected static string|\UnitEnum|null $navigationGroup = 'Pengaturan Laporan';
    protected static ?string $model = ReportType::class;

    public static function canViewAny(): bool { return auth()->user()?->hasPermissionTo('settings.manage') ?? false; }
    public static function canCreate(): bool { return static::canViewAny(); }
    public static function canEdit($record): bool { return static::canViewAny(); }
    public static function canDelete($record): bool { return false; }
    public static function canDeleteAny(): bool { return false; }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return ReportTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReportTypesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReportTypes::route('/'),
            'create' => CreateReportType::route('/create'),
            'edit' => EditReportType::route('/{record}/edit'),
        ];
    }
}
