<?php

namespace App\Observers;

use App\Models\ArchiveDocument;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\Auth;

class ArchiveDocumentObserver
{
    public function deleted(ArchiveDocument $document): void
    {
        app(AuditLogService::class)->record('archive.deleted', Auth::user(), $document);
    }

    public function restored(ArchiveDocument $document): void
    {
        app(AuditLogService::class)->record('archive.restored', Auth::user(), $document);
    }
}
