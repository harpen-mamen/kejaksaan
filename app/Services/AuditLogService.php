<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuditLogService
{
    /** @param array<string, scalar|null> $context */
    public function record(
        string $action,
        ?User $user = null,
        ?object $subject = null,
        string $status = 'success',
        array $context = [],
    ): AuditLog {
        $request = app()->bound('request') ? app(Request::class) : null;

        return AuditLog::query()->create([
            'user_id' => $user?->getKey(),
            'action' => $action,
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject && method_exists($subject, 'getKey') ? (string) $subject->getKey() : null,
            'status' => $status,
            'request_id' => $request?->attributes->get('request_id'),
            'context' => $context,
            'created_at' => now(),
        ]);
    }
}
