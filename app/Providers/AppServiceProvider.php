<?php

namespace App\Providers;

use App\Listeners\LogFailedLogin;
use App\Listeners\LogLogout;
use App\Listeners\LogSuccessfulLogin;
use App\Models\Activity;
use App\Models\ActivityCategory;
use App\Models\ArchiveDocument;
use App\Models\AuditLog;
use App\Models\BackupLog;
use App\Models\Role;
use App\Models\ReportType;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\ReportSubtype;
use App\Models\WorkOrderType;
use App\Models\ReminderRule;
use App\Models\Holiday;
use App\Observers\ActivityCategoryObserver;
use App\Observers\ActivityObserver;
use App\Observers\ArchiveDocumentObserver;
use App\Observers\WorkOrderObserver;
use App\Observers\ModelAuditObserver;
use App\Policies\ActivityCategoryPolicy;
use App\Policies\ActivityPolicy;
use App\Policies\ArchiveDocumentPolicy;
use App\Policies\AuditLogPolicy;
use App\Policies\BackupLogPolicy;
use App\Policies\RolePolicy;
use App\Policies\ReportTypePolicy;
use App\Policies\WorkOrderPolicy;
use App\Policies\UserPolicy;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(ActivityCategory::class, ActivityCategoryPolicy::class);
        Gate::policy(Activity::class, ActivityPolicy::class);
        Gate::policy(ArchiveDocument::class, ArchiveDocumentPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(AuditLog::class, AuditLogPolicy::class);
        Gate::policy(BackupLog::class, BackupLogPolicy::class);
        Gate::policy(ReportType::class, ReportTypePolicy::class);
        Gate::policy(WorkOrder::class, WorkOrderPolicy::class);

        Activity::observe(ActivityObserver::class);
        ActivityCategory::observe(ActivityCategoryObserver::class);
        ArchiveDocument::observe(ArchiveDocumentObserver::class);
        WorkOrder::observe(WorkOrderObserver::class);
        foreach ([User::class, Role::class, ReportType::class, ReportSubtype::class, WorkOrderType::class, ReminderRule::class, Holiday::class] as $model) {
            $model::observe(ModelAuditObserver::class);
        }

        Event::listen(Login::class, LogSuccessfulLogin::class);
        Event::listen(Failed::class, LogFailedLogin::class);
        Event::listen(Logout::class, LogLogout::class);
    }
}
