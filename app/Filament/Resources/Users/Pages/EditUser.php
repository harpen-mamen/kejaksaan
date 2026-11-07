<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;
    protected string $previousRoleSlugs = '';

    protected function beforeSave(): void
    {
        $this->previousRoleSlugs = $this->record->roles()->orderBy('slug')->pluck('slug')->implode(',');
    }

    protected function afterSave(): void
    {
        app(\App\Services\AuditLogService::class)->record('user.roles_changed', auth()->user(), $this->record, context: [
            'previous_role_slugs' => $this->previousRoleSlugs,
            'role_slugs' => $this->record->roles()->orderBy('slug')->pluck('slug')->implode(','),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [\Filament\Actions\DeleteAction::make()->visible(fn () => auth()->user()?->hasPermissionTo('users.manage') && auth()->id() !== $this->record->getKey())];
    }
}
