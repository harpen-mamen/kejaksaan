<?php

namespace App\Filament\Resources\Holidays\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class HolidaysTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')->label('Tanggal')->date()->sortable(),
                TextColumn::make('name')->label('Hari libur')->searchable(),
                TextColumn::make('description')->label('Catatan')->limit(60),
                TextColumn::make('is_active')->label('Aktif')->badge()->formatStateUsing(fn ($state) => $state ? 'Ya' : 'Tidak'),
            ])
            ->filters([
                
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ;
    }
}
