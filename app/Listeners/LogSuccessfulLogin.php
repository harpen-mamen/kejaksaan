<?php

namespace App\Listeners;

use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Auth\Events\Login;

class LogSuccessfulLogin
{
    public function handle(Login $event): void
    {
        app(AuditLogService::class)->record('auth.login.succeeded', $event->user instanceof User ? $event->user : null);
    }
}
