<?php

namespace App\Listeners;

use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Auth\Events\Logout;

class LogLogout
{
    public function handle(Logout $event): void
    {
        app(AuditLogService::class)->record('auth.logout', $event->user instanceof User ? $event->user : null);
    }
}
