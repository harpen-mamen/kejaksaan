<?php

namespace App\Filament\Resources\ReportTypes\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ReportTypesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Jenis')->searchable()->sortable(),
                TextColumn::make('code')->label('Kode')->searchable(),
                TextColumn::make('subtypes_count')->counts('subtypes')->label('Subjenis'),
                TextColumn::make('is_active')->label('Status')->badge()->formatStateUsing(fn ($state) => $state ? 'Aktif' : 'Nonaktif'),
            ])
            ->filters([
                
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ;
    }
}
