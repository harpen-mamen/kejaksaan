<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function view(User $user, User $record): bool { return $user->hasPermissionTo('users.view') || $user->hasPermissionTo('users.manage'); }
    public function viewAny(User $user): bool { return $user->hasPermissionTo('users.view') || $user->hasPermissionTo('users.manage'); }
    public function create(User $user): bool { return $user->hasPermissionTo('users.manage'); }
    public function update(User $user, User $record): bool { return $user->hasPermissionTo('users.manage'); }
    public function delete(User $user, User $record): bool { return $user->hasPermissionTo('users.manage') && $user->isNot($record); }
    public function deleteAny(User $user): bool { return false; }
    public function forceDelete(User $user, User $record): bool { return false; }
}
