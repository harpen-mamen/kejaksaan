<?php

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Roles\RoleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;

    protected function afterCreate(): void
    {
        app(\App\Services\AuditLogService::class)->record('role.permissions_assigned', auth()->user(), $this->record, context: [
            'permission_names' => $this->record->permissions()->orderBy('name')->pluck('name')->implode(','),
        ]);
    }
}
