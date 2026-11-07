<?php

namespace App\Filament\Resources\ActivityCategories;

use App\Filament\Resources\ActivityCategories\Pages\CreateActivityCategory;
use App\Filament\Resources\ActivityCategories\Pages\EditActivityCategory;
use App\Filament\Resources\ActivityCategories\Pages\ListActivityCategories;
use App\Filament\Resources\ActivityCategories\Schemas\ActivityCategoryForm;
use App\Filament\Resources\ActivityCategories\Tables\ActivityCategoriesTable;
use App\Models\ActivityCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ActivityCategoryResource extends Resource
{
    protected static ?string $modelLabel = 'kategori kegiatan';
    protected static ?string $pluralModelLabel = 'kategori kegiatan';
    protected static string|\UnitEnum|null $navigationGroup = 'Kegiatan';
    protected static ?string $model = ActivityCategory::class;

    public static function canViewAny(): bool { return auth()->user()?->hasPermissionTo('categories.view') ?? false; }
    public static function canCreate(): bool { return auth()->user()?->hasPermissionTo('categories.manage') ?? false; }
    public static function canEdit($record): bool { return auth()->user()?->hasPermissionTo('categories.manage') ?? false; }
    public static function canDelete($record): bool { return (auth()->user()?->hasPermissionTo('categories.manage') ?? false) && ! $record->isInUse(); }
    public static function canDeleteAny(): bool { return false; }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return ActivityCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ActivityCategoriesTable::configure($table);
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
            'index' => ListActivityCategories::route('/'),
            'create' => CreateActivityCategory::route('/create'),
            'edit' => EditActivityCategory::route('/{record}/edit'),
        ];
    }
}
