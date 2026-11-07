<?php

namespace App\Filament\Resources\WorkOrders\Tables;

use App\Models\WorkOrder;
use App\Services\WorkOrderCalendarService;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\EditAction;
use Filament\Tables\Table;

class WorkOrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')->label('Nomor')->searchable()->sortable(),
                TextColumn::make('type.name')->label('Jenis')->sortable(),
                TextColumn::make('start_date')->label('Mulai')->date()->sortable(),
                TextColumn::make('end_date')->label('Tanggal akhir')->date()->sortable(),
                TextColumn::make('end_date')->label('Sisa hari kerja')->state(fn (WorkOrder $record) => app(WorkOrderCalendarService::class)->workingDaysBetween(now(config('app.timezone'))->startOfDay(), $record->end_date))->sortable(),
                TextColumn::make('status')->label('Status')->state(fn (WorkOrder $record) => app(WorkOrderCalendarService::class)->status($record))->badge(),
                TextColumn::make('responsible.name')->label('Penanggung jawab'),
            ])
            ->filters([
                SelectFilter::make('status')->label('Status tersimpan')->options(['active' => 'Aktif', 'closed' => 'Ditutup']),
                SelectFilter::make('work_order_type_id')->label('Jenis')->relationship('type', 'name'),
                Filter::make('end_date_range')->label('Rentang tanggal akhir')->form([DatePicker::make('from')->label('Dari'), DatePicker::make('until')->label('Sampai')])->query(fn (Builder $query, array $data) => $query->when($data['from'] ?? null, fn (Builder $query, $date) => $query->whereDate('end_date', '>=', $date))->when($data['until'] ?? null, fn (Builder $query, $date) => $query->whereDate('end_date', '<=', $date))),
            ])
            ->recordActions([
                ViewAction::make()->url(fn (WorkOrder $record) => \App\Filament\Resources\WorkOrders\WorkOrderResource::getUrl('view', ['record' => $record->getKey()])),
                EditAction::make()->visible(fn () => auth()->user()?->hasPermissionTo('work_orders.manage')),
            ]);
    }
}
