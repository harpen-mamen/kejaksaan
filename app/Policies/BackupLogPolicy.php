<?php

namespace App\Policies;

use App\Models\BackupLog;
use App\Models\User;

class BackupLogPolicy
{
    public function view(User $user, BackupLog $log): bool { return $user->hasPermissionTo('backup.manage'); }
    public function viewAny(User $user): bool { return $user->hasPermissionTo('backup.manage'); }
    public function create(User $user): bool { return false; }
    public function update(User $user, BackupLog $log): bool { return false; }
    public function delete(User $user, BackupLog $log): bool { return false; }
    public function deleteAny(User $user): bool { return false; }
    public function forceDelete(User $user, BackupLog $log): bool { return false; }
}
