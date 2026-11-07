<?php

namespace App\Filament\Resources\WorkOrderReminders;

use App\Filament\Resources\WorkOrderReminders\Pages\ListWorkOrderReminders;
use App\Models\WorkOrderReminder;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class WorkOrderReminderResource extends Resource
{
    protected static ?string $model = WorkOrderReminder::class;
    protected static ?string $modelLabel = 'pengingat';
    protected static ?string $pluralModelLabel = 'Riwayat Pengingat';
    protected static ?string $navigationLabel = 'Pengingat';
    protected static string|\UnitEnum|null $navigationGroup = 'Tenggat dan Pengingat';
    protected static string|\BackedEnum|null $navigationIcon = \Filament\Support\Icons\Heroicon::OutlinedBell;

    public static function canViewAny(): bool { return auth()->user()?->hasPermissionTo('work_orders.view') ?? false; }
    public static function canCreate(): bool { return false; }
    public static function canEdit($record): bool { return false; }
    public static function canDelete($record): bool { return false; }
    public static function canDeleteAny(): bool { return false; }
    public static function canView($record): bool { return auth()->user()?->hasPermissionTo('work_orders.view') && (int) $record->user_id === (int) auth()->id(); }

    public static function form(Schema $schema): Schema { return $schema->components([]); }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->where('user_id', auth()->id())->with(['workOrder.type', 'rule']))
            ->columns([
                TextColumn::make('workOrder.number')->label('Nomor surat')->searchable()->sortable(),
                TextColumn::make('workOrder.type.name')->label('Jenis surat'),
                TextColumn::make('rule.name')->label('Pengingat'),
                TextColumn::make('end_date_snapshot')->label('Tanggal akhir')->date(),
                TextColumn::make('triggered_for')->label('Dibuat untuk')->date()->sortable(),
                TextColumn::make('read_at')->label('Status')->state(fn (WorkOrderReminder $record) => $record->invalidated_at ? 'Tidak berlaku (tanggal berubah)' : ($record->read_at ? 'Sudah dibaca' : 'Belum dibaca'))->badge(),
            ])
            ->filters([SelectFilter::make('read_at')->label('Status baca')->options(['unread' => 'Belum dibaca', 'read' => 'Sudah dibaca'])->query(fn ($query, array $data) => match ($data['value'] ?? null) { 'unread' => $query->whereNull('read_at'), 'read' => $query->whereNotNull('read_at'), default => $query })])
            ->recordActions([
                \Filament\Actions\Action::make('open')->label('Buka surat')->url(fn (WorkOrderReminder $record) => \App\Filament\Resources\WorkOrders\WorkOrderResource::getUrl('view', ['record' => $record->work_order_id]))->visible(fn (WorkOrderReminder $record) => auth()->user()?->can('view', $record->workOrder)),
                \Filament\Actions\Action::make('markRead')->label('Tandai dibaca')->visible(fn (WorkOrderReminder $record) => ! $record->read_at && ! $record->invalidated_at)->action(function (WorkOrderReminder $record): void {
                    abort_unless(auth()->user()?->hasPermissionTo('work_orders.view') && (int) $record->user_id === (int) auth()->id(), 403);
                    $record->update(['read_at' => now()]);
                    app(\App\Services\AuditLogService::class)->record('work_order.reminder_read', auth()->user(), $record);
                }),
            ]);
    }

    public static function getPages(): array { return ['index' => ListWorkOrderReminders::route('/')]; }
}
