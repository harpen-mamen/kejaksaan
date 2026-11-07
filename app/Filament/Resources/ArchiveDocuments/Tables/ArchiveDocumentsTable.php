<?php

namespace App\Filament\Resources\ArchiveDocuments\Tables;

use App\Models\ArchiveDocument;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use App\Models\ReportType;
use App\Models\ReportSubtype;

class ArchiveDocumentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('archive_code')->label('Nomor laporan')->searchable()->sortable(),
                TextColumn::make('document_number')->label('Referensi')->searchable()->toggleable(),
                TextColumn::make('title')->label('Perihal')->searchable()->limit(45),
                TextColumn::make('reportType.name')->label('Jenis laporan')->searchable()->sortable(),
                TextColumn::make('reportSubtype.name')->label('Subjenis')->searchable(),
                TextColumn::make('report_date')->label('Tanggal')->date()->sortable(),
                TextColumn::make('report_year')->label('Tahun')->sortable(),
                TextColumn::make('verification_status')->label('Pemeriksaan')->badge()->formatStateUsing(fn ($state) => ArchiveDocument::VERIFICATION_STATUSES[$state] ?? $state),
                TextColumn::make('createdBy.name')->label('Pembuat')->sortable(),
                TextColumn::make('updated_at')->label('Diubah')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('classification')->label('Jenis dan subjenis')->form([
                    \Filament\Forms\Components\Select::make('type_id')->label('Jenis laporan')->options(fn () => ReportType::query()->orderBy('name')->pluck('name', 'id'))->live()->afterStateUpdated(fn ($set) => $set('subtype_id', null)),
                    \Filament\Forms\Components\Select::make('subtype_id')->label('Subjenis/kategori')->options(fn ($get) => ReportSubtype::query()->when($get('type_id'), fn ($query, $typeId) => $query->where('report_type_id', $typeId))->orderBy('name')->pluck('name', 'id')),
                ])->query(fn (Builder $query, array $data) => $query
                    ->when($data['type_id'] ?? null, fn (Builder $query, $typeId) => $query->where('report_type_id', $typeId))
                    ->when($data['subtype_id'] ?? null, fn (Builder $query, $subtypeId) => $query->where('report_subtype_id', $subtypeId))),
                SelectFilter::make('created_by')->label('Pembuat')->relationship('createdBy', 'name'),
                SelectFilter::make('verification_status')->label('Status pemeriksaan')->options(ArchiveDocument::VERIFICATION_STATUSES),
                SelectFilter::make('report_year')->label('Tahun')->options(fn () => ArchiveDocument::query()->distinct()->orderByDesc('report_year')->pluck('report_year', 'report_year')->all()),
                Filter::make('report_date')->label('Tanggal laporan')->form([DatePicker::make('date')->label('Tanggal')])->query(fn (Builder $query, array $data) => $query->when($data['date'] ?? null, fn (Builder $query, $date) => $query->whereDate('report_date', $date))),
                Filter::make('report_date_range')->label('Rentang tanggal laporan')->form([DatePicker::make('from')->label('Dari'), DatePicker::make('until')->label('Sampai')])->query(fn (Builder $query, array $data) => $query->when($data['from'] ?? null, fn (Builder $query, $date) => $query->whereDate('report_date', '>=', $date))->when($data['until'] ?? null, fn (Builder $query, $date) => $query->whereDate('report_date', '<=', $date))),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('download')->label('Unduh')->icon('heroicon-o-arrow-down-tray')->url(fn (ArchiveDocument $record) => route('archives.download', $record))->visible(fn () => auth()->user()?->hasPermissionTo('archives.download')),
                DeleteAction::make()->visible(fn () => auth()->user()?->hasPermissionTo('archives.delete')),
                RestoreAction::make()->visible(fn () => auth()->user()?->hasPermissionTo('archives.delete')),
                Action::make('verify')->label('Periksa laporan')->visible(fn () => auth()->user()?->hasPermissionTo('archives.verify'))
                    ->form([\Filament\Forms\Components\Select::make('verification_status')->label('Status')->options(ArchiveDocument::VERIFICATION_STATUSES)->required()])
                    ->fillForm(fn (ArchiveDocument $record) => ['verification_status' => $record->verification_status])
                    ->action(function (ArchiveDocument $record, array $data): void {
                        \Illuminate\Support\Facades\Gate::authorize('verify', $record);
                        $record->update(['verification_status' => $data['verification_status'], 'updated_by' => auth()->id()]);
                        app(\App\Services\AuditLogService::class)->record('archive.verified', auth()->user(), $record, context: ['verification_status' => $data['verification_status']]);
                    }),
            ])
            ->paginated([10, 25, 50]);
    }
}
