<?php

namespace App\Filament\Resources\Activities\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ActivitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference_code')->label('Kode')->searchable()->sortable(),
                TextColumn::make('title')->label('Kegiatan')->searchable()->limit(50),
                TextColumn::make('category.name')->label('Kategori'),
                TextColumn::make('activity_date')->label('Tanggal')->date()->sortable(),
                TextColumn::make('activity_year')->label('Tahun')->sortable(),
                TextColumn::make('report_status')->label('Status')->badge(),
            ])
            ->filters([
                SelectFilter::make('activity_category_id')->label('Kategori')->relationship('category', 'name'),
                SelectFilter::make('report_status')->label('Status')->options(['belum_dikirim' => 'Belum dikirim', 'dikirim' => 'Dikirim', 'perlu_perbaikan' => 'Perlu perbaikan']),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()->visible(fn () => auth()->user()?->hasPermissionTo('activities.delete')),
                RestoreAction::make()->visible(fn () => auth()->user()?->hasPermissionTo('activities.delete')),
            ]);
    }
}
