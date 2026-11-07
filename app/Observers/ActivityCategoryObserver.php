<?php

namespace App\Observers;

use App\Models\ActivityCategory;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\Auth;

class ActivityCategoryObserver
{
    public function created(ActivityCategory $category): void
    {
        app(AuditLogService::class)->record('category.created', Auth::user(), $category);
    }

    public function updated(ActivityCategory $category): void
    {
        app(AuditLogService::class)->record('category.updated', Auth::user(), $category);
    }

    public function deleted(ActivityCategory $category): void
    {
        app(AuditLogService::class)->record('category.deleted', Auth::user(), $category);
    }
}
