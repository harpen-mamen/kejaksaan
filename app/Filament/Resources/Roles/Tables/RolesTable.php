<?php

namespace App\Filament\Resources\Roles\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RolesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Peran')->searchable()->sortable(),
                TextColumn::make('slug')->label('Kode')->searchable(),
                TextColumn::make('permissions_count')->counts('permissions')->label('Izin'),
                TextColumn::make('is_system')->label('Bawaan')->badge()->formatStateUsing(fn ($state) => $state ? 'Ya' : 'Tidak'),
            ])
            ->filters([
                
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ;
    }
}
