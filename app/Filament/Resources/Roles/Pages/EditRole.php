<?php

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Roles\RoleResource;
use Filament\Resources\Pages\EditRecord;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;
    protected string $previousPermissionNames = '';

    protected function beforeSave(): void
    {
        $this->previousPermissionNames = $this->record->permissions()->orderBy('name')->pluck('name')->implode(',');
    }

    protected function afterSave(): void
    {
        app(\App\Services\AuditLogService::class)->record('role.permissions_changed', auth()->user(), $this->record, context: [
            'previous_permission_names' => $this->previousPermissionNames,
            'permission_names' => $this->record->permissions()->orderBy('name')->pluck('name')->implode(','),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [\Filament\Actions\DeleteAction::make()->visible(fn () => auth()->user()?->hasPermissionTo('roles.manage') && ! $this->record->is_system && ! $this->record->users()->exists())];
    }
}
