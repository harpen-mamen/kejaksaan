<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function afterCreate(): void
    {
        app(\App\Services\AuditLogService::class)->record('user.roles_assigned', auth()->user(), $this->record, context: ['role_slugs' => $this->record->roles()->orderBy('slug')->pluck('slug')->implode(',')]);
    }
}
