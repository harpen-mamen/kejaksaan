<?php

namespace App\Filament\Widgets;

use App\Models\ArchiveDocument;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentArchivesWidget extends TableWidget
{
    protected static ?string $heading = 'Laporan Terbaru';
    protected int|string|array $columnSpan = 'full';
    public static function canView(): bool { return auth()->user()?->hasPermissionTo('archives.view') ?? false; }
    protected function getTableQuery(): Builder { return ArchiveDocument::query()->with(['reportType', 'reportSubtype'])->latest('uploaded_at'); }
    public function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('archive_code')->label('Nomor laporan')->searchable(),
            TextColumn::make('title')->label('Perihal')->limit(45),
            TextColumn::make('reportType.name')->label('Jenis laporan'),
            TextColumn::make('reportSubtype.name')->label('Subjenis'),
            TextColumn::make('report_date')->label('Tanggal laporan')->date(),
            TextColumn::make('createdBy.name')->label('Pembuat'),
            TextColumn::make('verification_status')->label('Pemeriksaan')->badge()->formatStateUsing(fn ($state) => ArchiveDocument::VERIFICATION_STATUSES[$state] ?? $state),
            TextColumn::make('created_at')->label('Dibuat')->dateTime()->sortable(),
        ])->paginated([5, 10]);
    }
}
