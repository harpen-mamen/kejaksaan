<?php

namespace App\Filament\Resources\ReportTypes\Pages;

use App\Filament\Resources\ReportTypes\ReportTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListReportTypes extends ListRecords
{
    protected static string $resource = ReportTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->visible(fn () => auth()->user()?->hasPermissionTo('settings.manage')),
        ];
    }
}
