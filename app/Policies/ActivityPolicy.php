<?php

namespace App\Policies;

use App\Models\Activity;
use App\Models\User;

class ActivityPolicy
{
    public function viewAny(User $user): bool { return $user->hasPermissionTo('activities.view'); }
    public function view(User $user, Activity $activity): bool { return $user->hasPermissionTo('activities.view'); }
    public function create(User $user): bool { return $user->hasPermissionTo('activities.create'); }
    public function update(User $user, Activity $activity): bool { return $user->hasPermissionTo('activities.update'); }
    public function delete(User $user, Activity $activity): bool { return $user->hasPermissionTo('activities.delete'); }
    public function deleteAny(User $user): bool { return false; }
    public function restore(User $user, Activity $activity): bool { return $user->hasPermissionTo('activities.delete'); }
    public function forceDelete(User $user, Activity $activity): bool { return false; }
}
