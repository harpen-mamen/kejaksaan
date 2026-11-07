<?php

namespace App\Observers;

use App\Models\WorkOrder;

class WorkOrderObserver
{
    public function updated(WorkOrder $workOrder): void
    {
        if ($workOrder->wasChanged('end_date')) {
            $workOrder->reminders()
                ->whereNull('read_at')
                ->whereNull('invalidated_at')
                ->where('end_date_snapshot', '<>', $workOrder->end_date->toDateString())
                ->update(['invalidated_at' => now()]);
        }
    }
}
