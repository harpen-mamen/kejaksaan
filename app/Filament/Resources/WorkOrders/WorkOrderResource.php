<?php

namespace App\Filament\Resources\WorkOrders;

use App\Filament\Resources\WorkOrders\Pages\CreateWorkOrder;
use App\Filament\Resources\WorkOrders\Pages\EditWorkOrder;
use App\Filament\Resources\WorkOrders\Pages\ListWorkOrders;
use App\Filament\Resources\WorkOrders\Pages\ViewWorkOrder;
use App\Filament\Resources\WorkOrders\Schemas\WorkOrderForm;
use App\Filament\Resources\WorkOrders\Tables\WorkOrdersTable;
use App\Models\WorkOrder;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Infolists\Components\TextEntry;

class WorkOrderResource extends Resource
{
    protected static ?string $modelLabel = 'surat perintah';
    protected static ?string $pluralModelLabel = 'surat perintah';
    protected static ?string $navigationLabel = 'Surat Perintah';
    protected static string|\UnitEnum|null $navigationGroup = 'Tenggat dan Pengingat';
    protected static ?string $model = WorkOrder::class;

    public static function canViewAny(): bool { return auth()->user()?->hasPermissionTo('work_orders.view') ?? false; }
    public static function canCreate(): bool { return auth()->user()?->hasPermissionTo('work_orders.manage') ?? false; }
    public static function canEdit($record): bool { return auth()->user()?->hasPermissionTo('work_orders.manage') ?? false; }
    public static function canDelete($record): bool { return false; }
    public static function canDeleteAny(): bool { return false; }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return WorkOrderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WorkOrdersTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('number')->label('Nomor surat'),
            TextEntry::make('type.name')->label('Jenis surat'),
            TextEntry::make('start_date')->label('Tanggal mulai')->date(),
            TextEntry::make('duration_days')->label('Durasi'),
            TextEntry::make('duration_basis')->label('Dasar durasi'),
            TextEntry::make('start_date_is_day_one')->label('Tanggal mulai dihitung hari pertama')->formatStateUsing(fn ($state) => $state ? 'Ya' : 'Tidak'),
            TextEntry::make('end_date')->label('Tanggal akhir dihitung')->date(),
            TextEntry::make('status')->label('Status')->state(fn (WorkOrder $record) => app(\App\Services\WorkOrderCalendarService::class)->status($record)),
            TextEntry::make('responsible.name')->label('Penanggung jawab')->placeholder('Belum ditentukan'),
            TextEntry::make('administrative_notes')->label('Catatan administratif')->columnSpanFull(),
            TextEntry::make('events_count')->label('Jumlah entri riwayat')->state(fn (WorkOrder $record) => $record->events()->count()),
        ]);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\WorkOrders\RelationManagers\EventsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWorkOrders::route('/'),
            'create' => CreateWorkOrder::route('/create'),
            'view' => ViewWorkOrder::route('/{record}'),
            'edit' => EditWorkOrder::route('/{record}/edit'),
        ];
    }
}
