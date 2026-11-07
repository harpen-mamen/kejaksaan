<?php

namespace App\Filament\Resources\WorkOrderReminders\Pages;

use App\Filament\Resources\WorkOrderReminders\WorkOrderReminderResource;
use Filament\Resources\Pages\ListRecords;

class ListWorkOrderReminders extends ListRecords
{
    protected static string $resource = WorkOrderReminderResource::class;
}
