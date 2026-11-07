<?php

namespace App\Filament\Resources\ArchiveDocuments\Pages;

use App\Filament\Resources\ArchiveDocuments\ArchiveDocumentResource;
use App\Services\ArchiveDocumentManager;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateArchiveDocument extends CreateRecord
{
    protected static string $resource = ArchiveDocumentResource::class;

    protected ?bool $hasDatabaseTransactions = false;

    protected function handleRecordCreation(array $data): Model
    {
        $upload = $data['upload'];
        unset($data['upload']);
        return app(ArchiveDocumentManager::class)->create($data, $upload, Auth::user());
    }
}
