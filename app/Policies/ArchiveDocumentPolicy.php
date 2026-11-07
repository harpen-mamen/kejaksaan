<?php

namespace App\Policies;

use App\Models\ArchiveDocument;
use App\Models\User;

class ArchiveDocumentPolicy
{
    public function viewAny(User $user): bool { return $user->hasPermissionTo('archives.view'); }
    public function view(User $user, ArchiveDocument $document): bool { return $user->hasPermissionTo('archives.view'); }
    public function create(User $user): bool { return $user->hasPermissionTo('archives.upload'); }
    public function update(User $user, ArchiveDocument $document): bool { return $user->hasPermissionTo('archives.update'); }
    public function delete(User $user, ArchiveDocument $document): bool { return $user->hasPermissionTo('archives.delete'); }
    public function deleteAny(User $user): bool { return false; }
    public function restore(User $user, ArchiveDocument $document): bool { return $user->hasPermissionTo('archives.delete'); }
    public function forceDelete(User $user, ArchiveDocument $document): bool { return false; }
    public function download(User $user, ArchiveDocument $document): bool { return $user->hasPermissionTo('archives.download'); }
    public function verify(User $user, ArchiveDocument $document): bool { return $user->hasPermissionTo('archives.verify'); }
}
