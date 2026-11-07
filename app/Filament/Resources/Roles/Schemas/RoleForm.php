<?php

namespace App\Filament\Resources\Roles\Schemas;

use App\Models\Permission;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->label('Nama peran')->required()->maxLength(120),
                TextInput::make('slug')->label('Kode peran')->required()->maxLength(120)->alphaDash()->unique(ignoreRecord: true),
                Textarea::make('description')->label('Deskripsi')->maxLength(1000),
                Select::make('permissions')->label('Izin')->multiple()->relationship('permissions', 'label')->options(fn () => Permission::orderBy('name')->pluck('label', 'id'))->searchable()->preload()->columnSpanFull(),
            ]);
    }
}
