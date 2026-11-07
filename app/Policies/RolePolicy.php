<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

class RolePolicy
{
    public function view(User $user, Role $role): bool { return $user->hasPermissionTo('roles.manage'); }
    public function viewAny(User $user): bool { return $user->hasPermissionTo('roles.manage'); }
    public function create(User $user): bool { return $user->hasPermissionTo('roles.manage'); }
    public function update(User $user, Role $role): bool { return $user->hasPermissionTo('roles.manage'); }
    public function delete(User $user, Role $role): bool { return $user->hasPermissionTo('roles.manage') && ! $role->is_system && ! $role->users()->exists(); }
    public function deleteAny(User $user): bool { return false; }
    public function forceDelete(User $user, Role $role): bool { return false; }
}
