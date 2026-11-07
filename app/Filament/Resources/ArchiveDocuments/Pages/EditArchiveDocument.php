<?php

namespace App\Filament\Resources\ArchiveDocuments\Pages;

use App\Filament\Resources\ArchiveDocuments\ArchiveDocumentResource;
use App\Services\ArchiveDocumentManager;
use Illuminate\Database\Eloquent\Model;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditArchiveDocument extends EditRecord
{
    protected static string $resource = ArchiveDocumentResource::class;

    protected ?bool $hasDatabaseTransactions = false;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $upload = $data['upload'] ?? null;
        unset($data['upload']);
        return app(ArchiveDocumentManager::class)->update($record, $data, $upload, Auth::user());
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->visible(fn () => auth()->user()?->hasPermissionTo('archives.delete')),
            RestoreAction::make()->visible(fn () => auth()->user()?->hasPermissionTo('archives.delete') && $this->record->trashed()),
        ];
    }
}
