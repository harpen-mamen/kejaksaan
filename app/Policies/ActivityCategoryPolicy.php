<?php

namespace App\Policies;

use App\Models\ActivityCategory;
use App\Models\User;

class ActivityCategoryPolicy
{
    public function viewAny(User $user): bool { return $user->hasPermissionTo('categories.view'); }
    public function view(User $user, ActivityCategory $category): bool { return $user->hasPermissionTo('categories.view'); }
    public function create(User $user): bool { return $user->hasPermissionTo('categories.manage'); }
    public function update(User $user, ActivityCategory $category): bool { return $user->hasPermissionTo('categories.manage'); }
    public function delete(User $user, ActivityCategory $category): bool { return $user->hasPermissionTo('categories.manage') && ! $category->isInUse(); }
    public function deleteAny(User $user): bool { return false; }
}
