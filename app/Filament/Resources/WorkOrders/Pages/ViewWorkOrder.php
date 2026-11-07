<?php

namespace App\Filament\Resources\WorkOrders\Pages;

use App\Filament\Resources\WorkOrders\WorkOrderResource;
use App\Services\WorkOrderManager;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewWorkOrder extends ViewRecord
{
    protected static string $resource = WorkOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('extend')
                ->label('Catat perpanjangan')
                ->color('warning')
                ->visible(fn () => (auth()->user()?->hasPermissionTo('work_orders.extend') ?? false) && $this->record->status === 'active')
                ->form([
                    TextInput::make('additional_days')->label('Tambahan hari')->numeric()->minValue(1)->required(),
                    TextInput::make('approval_reference')->label('Referensi persetujuan')->required()->maxLength(150),
                    Textarea::make('reason')->label('Alasan')->required(),
                ])
                ->action(fn (array $data) => app(WorkOrderManager::class)->extend($this->record, (int) $data['additional_days'], $data['approval_reference'], $data['reason'], Auth::user())),
            Action::make('close')
                ->label('Tutup surat')
                ->color('danger')
                ->visible(fn () => (auth()->user()?->hasPermissionTo('work_orders.close') ?? false) && $this->record->status === 'active')
                ->form([Textarea::make('reason')->label('Alasan penutupan')->required()])
                ->action(fn (array $data) => app(WorkOrderManager::class)->close($this->record, $data['reason'], Auth::user())),
        ];
    }
}
