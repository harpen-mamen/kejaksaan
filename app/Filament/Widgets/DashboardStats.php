<?php

namespace App\Filament\Widgets;

use App\Models\ArchiveDocument;
use App\Models\WorkOrder;
use App\Models\WorkOrderReminder;
use App\Models\ReportType;
use App\Services\WorkOrderCalendarService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $user = auth()->user();
        $stats = [];
        if ($user?->hasPermissionTo('archives.view')) {
            $stats[] = Stat::make('Total laporan', ArchiveDocument::query()->count())->color('primary');
            foreach (ReportType::query()->where(fn ($query) => $query->where('is_active', true)->orWhereHas('documents'))->withCount('documents')->orderBy('name')->get() as $type) {
                $stats[] = Stat::make('Laporan - '.$type->name, $type->documents_count)->color('primary');
            }
        }
        if ($user?->hasPermissionTo('work_orders.view')) {
            $calendar = app(WorkOrderCalendarService::class);
            $active = WorkOrder::query()->where('status', 'active')->get();
            $stats[] = Stat::make('Surat perintah aktif', $active->filter(fn (WorkOrder $item) => ! in_array($calendar->status($item), ['berakhir', 'ditutup'], true))->count())->color('success');
            $stats[] = Stat::make('Mendekati tenggat', $active->filter(fn (WorkOrder $item) => in_array($calendar->status($item), ['mendekati_tenggat', 'tenggat_hari_ini'], true))->count())->color('warning');
            $stats[] = Stat::make('Surat berakhir', $active->filter(fn (WorkOrder $item) => $calendar->status($item) === 'berakhir')->count())->color('danger');
            $stats[] = Stat::make('Pengingat hari ini', WorkOrderReminder::query()->where('user_id', $user->getKey())->whereDate('triggered_for', now(config('app.timezone'))->toDateString())->whereNull('invalidated_at')->count())->color('warning');
            $stats[] = Stat::make('Belum dibaca', WorkOrderReminder::query()->where('user_id', $user->getKey())->whereNull('read_at')->whereNull('invalidated_at')->count())->color('primary');
        }
        return $stats;
    }
}
