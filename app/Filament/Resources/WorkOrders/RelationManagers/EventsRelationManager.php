<?php

namespace App\Filament\Resources\WorkOrders\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EventsRelationManager extends RelationManager
{
    protected static string $relationship = 'events';
    protected static ?string $title = 'Histori Perubahan Surat';

    public function table(Table $table): Table
    {
        return $table->defaultSort('created_at', 'desc')->columns([
            TextColumn::make('created_at')->label('Waktu')->dateTime()->sortable(),
            TextColumn::make('event_type')->label('Kejadian')->badge(),
            TextColumn::make('previous_end_date')->label('Tanggal akhir sebelumnya')->date()->placeholder('—'),
            TextColumn::make('new_end_date')->label('Tanggal akhir baru')->date()->placeholder('—'),
            TextColumn::make('previous_duration_days')->label('Durasi sebelumnya')->placeholder('—'),
            TextColumn::make('new_duration_days')->label('Durasi baru')->placeholder('—'),
            TextColumn::make('approval_reference')->label('Referensi persetujuan')->placeholder('—'),
            TextColumn::make('reason')->label('Alasan')->limit(70)->placeholder('—'),
            TextColumn::make('user.name')->label('Pengguna')->placeholder('Sistem'),
        ]);
    }
}
