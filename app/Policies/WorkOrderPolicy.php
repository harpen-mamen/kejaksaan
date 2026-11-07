<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkOrder;

class WorkOrderPolicy
{
    public function view(User $user, WorkOrder $record): bool { return $user->hasPermissionTo('work_orders.view'); }
    public function viewAny(User $user): bool { return $user->hasPermissionTo('work_orders.view'); }
    public function create(User $user): bool { return $user->hasPermissionTo('work_orders.manage'); }
    public function update(User $user, WorkOrder $record): bool { return $user->hasPermissionTo('work_orders.manage'); }
    public function extend(User $user, WorkOrder $record): bool { return $user->hasPermissionTo('work_orders.extend'); }
    public function close(User $user, WorkOrder $record): bool { return $user->hasPermissionTo('work_orders.close'); }
    public function delete(User $user, WorkOrder $record): bool { return false; }
    public function deleteAny(User $user): bool { return false; }
    public function forceDelete(User $user, WorkOrder $record): bool { return false; }
}
