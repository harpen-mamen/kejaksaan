<?php

namespace App\Filament\Resources\Activities;

use App\Filament\Resources\Activities\Pages\CreateActivity;
use App\Filament\Resources\Activities\Pages\EditActivity;
use App\Filament\Resources\Activities\Pages\ListActivities;
use App\Filament\Resources\Activities\Schemas\ActivityForm;
use App\Filament\Resources\Activities\Tables\ActivitiesTable;
use App\Models\Activity;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ActivityResource extends Resource
{
    protected static ?string $modelLabel = 'kegiatan';
    protected static ?string $pluralModelLabel = 'kegiatan';
    protected static string|\UnitEnum|null $navigationGroup = 'Kegiatan';
    protected static ?string $model = Activity::class;

    public static function canViewAny(): bool { return auth()->user()?->hasPermissionTo('activities.view') ?? false; }
    public static function canCreate(): bool { return auth()->user()?->hasPermissionTo('activities.create') ?? false; }
    public static function canEdit($record): bool { return auth()->user()?->hasPermissionTo('activities.update') ?? false; }
    public static function canDelete($record): bool { return auth()->user()?->hasPermissionTo('activities.delete') ?? false; }
    public static function canDeleteAny(): bool { return false; }
    public static function canForceDelete($record): bool { return false; }
    public static function canRestore($record): bool { return auth()->user()?->hasPermissionTo('activities.delete') ?? false; }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return ActivityForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ActivitiesTable::configure($table);
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
            'index' => ListActivities::route('/'),
            'create' => CreateActivity::route('/create'),
            'edit' => EditActivity::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
