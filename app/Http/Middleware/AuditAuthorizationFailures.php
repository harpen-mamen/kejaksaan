<?php

namespace App\Http\Middleware;

use App\Services\AuditLogService;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditAuthorizationFailures
{
    public function __construct(private readonly AuditLogService $auditLog) {}

    public function handle(Request $request, Closure $next): Response
    {
        try {
            return $next($request);
        } catch (AuthorizationException $exception) {
            $this->auditLog->record('access.denied', $request->user(), status: 'denied');

            throw $exception;
        }
    }
}
