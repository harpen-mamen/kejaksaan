<?php

namespace App\Listeners;

use App\Services\AuditLogService;
use Illuminate\Auth\Events\Failed;

class LogFailedLogin
{
    public function handle(Failed $event): void
    {
        // Never inspect or store the submitted credentials (which include the password).
        app(AuditLogService::class)->record(
            'auth.login.failed',
            $event->user instanceof \App\Models\User ? $event->user : null,
            status: 'failed',
        );
    }
}
