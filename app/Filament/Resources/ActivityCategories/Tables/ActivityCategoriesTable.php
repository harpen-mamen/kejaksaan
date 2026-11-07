<?php

namespace App\Filament\Resources\ActivityCategories\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ActivityCategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Kategori')->searchable()->sortable(),
                TextColumn::make('code')->label('Kode')->searchable(),
                TextColumn::make('activities_count')->counts('activities')->label('Kegiatan'),
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
