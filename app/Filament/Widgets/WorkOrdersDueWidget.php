<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\WorkOrders\WorkOrderResource;
use App\Models\WorkOrder;
use App\Services\WorkOrderCalendarService;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class WorkOrdersDueWidget extends TableWidget
{
    protected static ?string $heading = 'Perlu Ditindaklanjuti';
    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool { return auth()->user()?->hasPermissionTo('work_orders.view') ?? false; }

    protected function getTableQuery(): Builder
    {
        return WorkOrder::query()->with('type', 'responsible')->where('status', 'active')->orderBy('end_date');
    }

    protected function isTablePaginationEnabled(): bool { return true; }

    public function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('number')->label('Nomor surat')->searchable(),
            TextColumn::make('type.name')->label('Jenis surat'),
            TextColumn::make('start_date')->label('Tanggal mulai')->date(),
            TextColumn::make('end_date')->label('Tanggal akhir')->date()->sortable(),
            TextColumn::make('end_date')->label('Sisa hari kerja')->state(fn (WorkOrder $record) => app(WorkOrderCalendarService::class)->workingDaysBetween(now(config('app.timezone'))->startOfDay(), $record->end_date)),
            TextColumn::make('status')->label('Status')->state(fn (WorkOrder $record) => app(WorkOrderCalendarService::class)->status($record))->badge(),
        ])->recordActions([
            Action::make('open')->label('Detail')->url(fn (WorkOrder $record) => WorkOrderResource::getUrl('view', ['record' => $record->getKey()])),
        ])->modifyQueryUsing(fn (Builder $query) => $query->where(function (Builder $query): void {
            $today = now(config('app.timezone'))->toDateString();
            $query->whereDate('end_date', '<=', now(config('app.timezone'))->addDays(14)->toDateString())->orWhereDate('end_date', '<', $today);
        }));
    }
}
