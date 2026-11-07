<?php

namespace App\Observers;

use App\Models\Activity;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\Auth;

class ActivityObserver
{
    public function creating(Activity $activity): void
    {
        $activity->activity_year = $activity->activity_date?->year ?? now()->year;
        $activity->created_by ??= Auth::id();
        $activity->updated_by ??= Auth::id();
    }

    public function updating(Activity $activity): void
    {
        $activity->activity_year = $activity->activity_date?->year ?? now()->year;
        $activity->updated_by = Auth::id() ?? $activity->updated_by;
    }

    public function created(Activity $activity): void
    {
        app(AuditLogService::class)->record('activity.created', Auth::user(), $activity);
    }

    public function updated(Activity $activity): void
    {
        app(AuditLogService::class)->record('activity.updated', Auth::user(), $activity);
    }

    public function deleted(Activity $activity): void
    {
        app(AuditLogService::class)->record('activity.deleted', Auth::user(), $activity);
    }

    public function restored(Activity $activity): void
    {
        app(AuditLogService::class)->record('activity.restored', Auth::user(), $activity);
    }
}
