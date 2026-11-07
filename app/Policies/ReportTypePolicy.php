<?php

namespace App\Policies;

use App\Models\ReportType;
use App\Models\User;

class ReportTypePolicy
{
    private function allowed(User $user): bool { return $user->hasPermissionTo('settings.manage'); }
    public function view(User $user, ReportType $record): bool { return $this->allowed($user); }
    public function viewAny(User $user): bool { return $this->allowed($user); }
    public function create(User $user): bool { return $this->allowed($user); }
    public function update(User $user, ReportType $record): bool { return $this->allowed($user); }
    public function delete(User $user, ReportType $record): bool { return false; }
    public function deleteAny(User $user): bool { return false; }
}
