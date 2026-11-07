<?php

namespace App\Filament\Resources\WorkOrders\Schemas;

use App\Models\User;
use App\Models\WorkOrderType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class WorkOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('number')->label('Nomor surat')->required()->maxLength(100)->unique(ignoreRecord: true),
                Select::make('work_order_type_id')->label('Jenis surat')->options(fn () => WorkOrderType::where('is_active', true)->orderBy('name')->pluck('name', 'id'))->live()->afterStateUpdated(fn ($state, $set) => $set('duration_days', WorkOrderType::find($state)?->default_duration_days))->required(),
                DatePicker::make('start_date')->label('Mulai berlaku')->required()->native(false),
                TextInput::make('duration_days')->label('Durasi hari')->numeric()->minValue(1)->required()->default(fn ($get) => WorkOrderType::find($get('work_order_type_id'))?->default_duration_days),
                Select::make('responsible_user_id')->label('Penanggung jawab')->options(fn () => User::where('is_active', true)->orderBy('name')->pluck('name', 'id'))->searchable()->nullable(),
                Textarea::make('administrative_notes')->label('Catatan administratif')->maxLength(2000)->columnSpanFull(),
            ]);
    }
}
