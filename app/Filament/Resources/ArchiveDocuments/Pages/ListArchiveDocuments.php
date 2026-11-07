<?php

namespace App\Filament\Resources\ArchiveDocuments\Pages;

use App\Filament\Resources\ArchiveDocuments\ArchiveDocumentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListArchiveDocuments extends ListRecords
{
    protected static string $resource = ArchiveDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->visible(fn () => auth()->user()?->hasPermissionTo('archives.upload')),
        ];
    }
}
