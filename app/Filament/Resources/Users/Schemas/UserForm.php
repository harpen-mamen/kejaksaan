<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\Role;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->label('Nama pengguna')->required()->maxLength(255),
                TextInput::make('email')->label('Email login')->email()->required()->maxLength(255)->unique(ignoreRecord: true),
                TextInput::make('password')->label('Kata sandi')->password()->revealable()->required(fn (string $operation) => $operation === 'create')->minLength(12)->dehydrated(fn ($state) => filled($state)),
                Select::make('roles')->label('Peran')->multiple()->relationship('roles', 'name')->options(fn () => Role::orderBy('name')->pluck('name', 'id'))->searchable()->preload()
                    ->disabled(fn (?User $record) => ! (auth()->user()?->hasPermissionTo('roles.manage') ?? false) || auth()->id() === $record?->getKey())
                    ->dehydrated(fn (?User $record) => (auth()->user()?->hasPermissionTo('roles.manage') ?? false) && auth()->id() !== $record?->getKey())
                    ->required(fn (string $operation) => $operation === 'create' && (auth()->user()?->hasPermissionTo('roles.manage') ?? false)),
                Toggle::make('is_active')->label('Akses aktif')->default(true)
                    ->disabled(fn (?User $record) => auth()->id() === $record?->getKey())
                    ->dehydrated(fn (?User $record) => auth()->id() !== $record?->getKey()),
            ]);
    }
}
