<?php

namespace App\Filament\Resources\ActivityCategories\Pages;

use App\Filament\Resources\ActivityCategories\ActivityCategoryResource;
use Filament\Resources\Pages\EditRecord;

class EditActivityCategory extends EditRecord
{
    protected static string $resource = ActivityCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\DeleteAction::make()->visible(fn () => auth()->user()?->hasPermissionTo('categories.manage') && ! $this->record->isInUse()),
        ];
    }
}
