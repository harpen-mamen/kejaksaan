<?php

namespace App\Filament\Resources\ReportTypes\Pages;

use App\Filament\Resources\ReportTypes\ReportTypeResource;
use Filament\Resources\Pages\EditRecord;

class EditReportType extends EditRecord
{
    protected static string $resource = ReportTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\DeleteAction::make()->visible(fn () => false),
        ];
    }
}
