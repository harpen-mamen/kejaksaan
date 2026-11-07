<?php

namespace App\Services;

use App\Models\WorkOrder;
use App\Models\WorkOrderEvent;
use App\Models\WorkOrderType;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class WorkOrderManager
{
    public function __construct(
        private readonly WorkOrderCalendarService $calendar,
        private readonly AuditLogService $audit,
    ) {}

    public function create(array $data, User $actor): WorkOrder
    {
        Gate::forUser($actor)->authorize('create', WorkOrder::class);

        $type = WorkOrderType::query()->whereKey($data['work_order_type_id'])->where('is_active', true)->first();
        if (! $type) {
            throw ValidationException::withMessages(['work_order_type_id' => 'Jenis surat perintah tidak aktif atau tidak ditemukan.']);
        }

        return DB::transaction(function () use ($data, $actor, $type): WorkOrder {
            $workOrder = new WorkOrder([
                'number' => $data['number'],
                'work_order_type_id' => $type->getKey(),
                'start_date' => $data['start_date'],
                'responsible_user_id' => $data['responsible_user_id'] ?? null,
                'administrative_notes' => $data['administrative_notes'] ?? null,
                'duration_days' => $data['duration_days'] ?? $type->default_duration_days,
                'duration_basis' => $type->duration_basis,
                'start_date_is_day_one' => $type->start_date_is_day_one,
                'status' => 'active',
                'created_by' => $actor->getKey(),
                'updated_by' => $actor->getKey(),
            ]);
            $workOrder->calculateEndDate();
            $workOrder->save();

            $workOrder->events()->create([
                'user_id' => $actor->getKey(),
                'event_type' => 'created',
                'new_end_date' => $workOrder->end_date,
                'new_duration_days' => $workOrder->duration_days,
                'created_at' => now(),
            ]);
            $this->audit->record('work_order.created', $actor, $workOrder);
            return $workOrder;
        });
    }

    public function update(WorkOrder $workOrder, array $data, User $actor): WorkOrder
    {
        Gate::forUser($actor)->authorize('update', $workOrder);

        return DB::transaction(function () use ($workOrder, $data, $actor): WorkOrder {
            $workOrder = WorkOrder::query()->lockForUpdate()->findOrFail($workOrder->getKey());
            $previousEnd = $workOrder->end_date->toDateString();
            $previousDuration = $workOrder->duration_days;

            if ((int) $data['work_order_type_id'] !== $workOrder->work_order_type_id) {
                $type = WorkOrderType::query()
                    ->whereKey($data['work_order_type_id'])
                    ->where('is_active', true)
                    ->first();
                if (! $type) {
                    throw ValidationException::withMessages(['work_order_type_id' => 'Jenis surat perintah tidak aktif atau tidak ditemukan.']);
                }
                $workOrder->duration_basis = $type->duration_basis;
                $workOrder->start_date_is_day_one = $type->start_date_is_day_one;
            }

            $workOrder->fill([
                'number' => $data['number'],
                'work_order_type_id' => $data['work_order_type_id'],
                'start_date' => $data['start_date'],
                'duration_days' => $data['duration_days'],
                'responsible_user_id' => $data['responsible_user_id'] ?? null,
                'administrative_notes' => $data['administrative_notes'] ?? null,
                'updated_by' => $actor->getKey(),
            ]);
            $workOrder->calculateEndDate();
            $workOrder->save();

            if ($workOrder->wasChanged(['end_date', 'duration_days', 'duration_basis', 'start_date', 'start_date_is_day_one'])) {
                $workOrder->events()->create([
                    'user_id' => $actor->getKey(),
                    'event_type' => 'terms_changed',
                    'previous_end_date' => $previousEnd,
                    'new_end_date' => $workOrder->end_date,
                    'previous_duration_days' => $previousDuration,
                    'new_duration_days' => $workOrder->duration_days,
                    'reason' => 'Perubahan administratif melalui pembaruan surat perintah.',
                    'created_at' => now(),
                ]);
            }
            $this->audit->record('work_order.updated', $actor, $workOrder);

            return $workOrder->refresh();
        });
    }

    public function extend(WorkOrder $workOrder, int $additionalDays, string $approvalReference, string $reason, User $actor): WorkOrder
    {
        Gate::forUser($actor)->authorize('extend', $workOrder);

        if ($additionalDays < 1 || blank($approvalReference) || blank($reason)) {
            throw ValidationException::withMessages(['approval_reference' => 'Durasi tambahan, referensi persetujuan, dan alasan wajib diisi.']);
        }

        return DB::transaction(function () use ($workOrder, $additionalDays, $approvalReference, $reason, $actor): WorkOrder {
            $workOrder = WorkOrder::query()->lockForUpdate()->findOrFail($workOrder->getKey());
            if ($workOrder->status !== 'active') {
                throw ValidationException::withMessages(['work_order' => 'Surat yang ditutup tidak dapat diperpanjang.']);
            }

            $previousEnd = $workOrder->end_date->toDateString();
            $previousDuration = $workOrder->duration_days;
            $workOrder->duration_days += $additionalDays;
            $workOrder->updated_by = $actor->getKey();
            $workOrder->calculateEndDate();
            $workOrder->save();

            $workOrder->events()->create([
                'user_id' => $actor->getKey(),
                'event_type' => 'extension',
                'previous_end_date' => $previousEnd,
                'new_end_date' => $workOrder->end_date,
                'previous_duration_days' => $previousDuration,
                'new_duration_days' => $workOrder->duration_days,
                'approval_reference' => mb_substr($approvalReference, 0, 150),
                'reason' => $reason,
                'created_at' => now(),
            ]);
            $this->audit->record('work_order.extended', $actor, $workOrder, context: ['additional_days' => $additionalDays]);

            return $workOrder->refresh();
        });
    }

    public function close(WorkOrder $workOrder, string $reason, User $actor): WorkOrder
    {
        Gate::forUser($actor)->authorize('close', $workOrder);

        if (blank($reason)) {
            throw ValidationException::withMessages(['reason' => 'Alasan penutupan wajib diisi.']);
        }

        return DB::transaction(function () use ($workOrder, $reason, $actor): WorkOrder {
            $workOrder = WorkOrder::query()->lockForUpdate()->findOrFail($workOrder->getKey());
            if ($workOrder->status === 'closed') {
                return $workOrder;
            }

            $workOrder->update([
                'status' => 'closed',
                'closed_at' => CarbonImmutable::now(config('app.timezone'))->toDateString(),
                'updated_by' => $actor->getKey(),
            ]);
            $workOrder->events()->create([
                'user_id' => $actor->getKey(),
                'event_type' => 'closed',
                'previous_end_date' => $workOrder->end_date,
                'new_end_date' => $workOrder->end_date,
                'reason' => $reason,
                'created_at' => now(),
            ]);
            $this->audit->record('work_order.closed', $actor, $workOrder);

            return $workOrder->refresh();
        });
    }
}
