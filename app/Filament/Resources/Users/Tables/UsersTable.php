<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nama')->searchable()->sortable(),
                TextColumn::make('email')->label('Email')->searchable(),
                TextColumn::make('roles.name')->label('Peran')->badge(),
                TextColumn::make('is_active')->label('Akses')->badge()->formatStateUsing(fn ($state) => $state ? 'Aktif' : 'Nonaktif'),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label('Akses aktif'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ;
    }
}
