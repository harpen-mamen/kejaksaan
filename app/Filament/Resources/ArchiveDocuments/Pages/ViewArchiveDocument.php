<?php

namespace App\Filament\Resources\ArchiveDocuments\Pages;

use App\Filament\Resources\ArchiveDocuments\ArchiveDocumentResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewArchiveDocument extends ViewRecord
{
    protected static string $resource = ArchiveDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('download')
                ->label('Unduh berkas laporan')
                ->icon('heroicon-o-arrow-down-tray')
                ->url(fn () => route('archives.download', $this->record))
                ->visible(fn () => auth()->user()?->hasPermissionTo('archives.download') ?? false),
        ];
    }
}
