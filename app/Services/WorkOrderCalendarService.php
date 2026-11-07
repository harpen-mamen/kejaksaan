<?php

namespace App\Services;

use App\Models\Holiday;
use App\Models\WorkOrder;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use InvalidArgumentException;

class WorkOrderCalendarService
{
    public function calculateEndDate(
        CarbonInterface|string $startDate,
        int $durationDays,
        string $durationBasis = 'calendar_days',
        bool $startDateIsDayOne = false,
    ): CarbonImmutable {
        if ($durationDays < 1) {
            throw new InvalidArgumentException('Durasi harus minimal satu hari.');
        }

        $timezone = config('app.timezone', 'Asia/Makassar');
        $start = CarbonImmutable::parse($startDate, $timezone)->startOfDay();

        if ($durationBasis === 'calendar_days') {
            return $start->addDays($durationDays - ($startDateIsDayOne ? 1 : 0));
        }

        if ($durationBasis !== 'working_days') {
            throw new InvalidArgumentException('Dasar masa berlaku tidak dikenal.');
        }

        $remaining = $durationDays;
        if ($startDateIsDayOne && $this->isWorkingDay($start)) {
            $remaining--;
        }

        if ($remaining === 0) {
            return $start;
        }

        $cursor = $start;
        while ($remaining > 0) {
            $cursor = $cursor->addDay();
            if ($this->isWorkingDay($cursor)) {
                $remaining--;
            }
        }

        return $cursor;
    }

    public function isWorkingDay(CarbonInterface|string $date): bool
    {
        $day = CarbonImmutable::parse($date, config('app.timezone', 'Asia/Makassar'))->startOfDay();

        if ($day->isWeekend()) {
            return false;
        }

        return ! Holiday::query()->whereDate('date', $day->toDateString())->where('is_active', true)->exists();
    }

    public function reminderTriggerDate(CarbonInterface|string $endDate, int $workingDaysBefore): CarbonImmutable
    {
        $cursor = CarbonImmutable::parse($endDate, config('app.timezone', 'Asia/Makassar'))->startOfDay();
        if ($workingDaysBefore < 0) {
            throw new InvalidArgumentException('Jarak pengingat tidak boleh negatif.');
        }

        $remaining = $workingDaysBefore;
        while ($remaining > 0) {
            $cursor = $cursor->subDay();
            if ($this->isWorkingDay($cursor)) {
                $remaining--;
            }
        }

        return $cursor;
    }

    /** Count working dates strictly after $from and through $to. */
    public function workingDaysBetween(CarbonInterface|string $from, CarbonInterface|string $to): int
    {
        $cursor = CarbonImmutable::parse($from, config('app.timezone', 'Asia/Makassar'))->startOfDay();
        $end = CarbonImmutable::parse($to, config('app.timezone', 'Asia/Makassar'))->startOfDay();
        if ($end->lessThanOrEqualTo($cursor)) {
            return 0;
        }

        $count = 0;
        while ($cursor->lessThan($end)) {
            $cursor = $cursor->addDay();
            if ($this->isWorkingDay($cursor)) {
                $count++;
            }
        }

        return $count;
    }

    public function status(WorkOrder $workOrder, CarbonInterface|string|null $asOf = null): string
    {
        if ($workOrder->status === 'closed' || $workOrder->closed_at) {
            return 'ditutup';
        }

        $today = CarbonImmutable::parse($asOf ?? now(config('app.timezone')), config('app.timezone'))->startOfDay();
        $endDate = CarbonImmutable::parse($workOrder->end_date, config('app.timezone'))->startOfDay();

        if ($endDate->lessThan($today)) {
            return 'berakhir';
        }

        if ($endDate->equalTo($today)) {
            return 'tenggat_hari_ini';
        }

        $nearDeadlineThreshold = (int) (\App\Models\ReminderRule::query()->where('is_active', true)->max('working_days_before') ?? 10);
        return $this->workingDaysBetween($today, $endDate) <= $nearDeadlineThreshold ? 'mendekati_tenggat' : 'aktif';
    }

    public function remindersDueOn(CarbonInterface|string $date): array
    {
        $today = CarbonImmutable::parse($date, config('app.timezone', 'Asia/Makassar'))->startOfDay();
        $rules = \App\Models\ReminderRule::query()->where('is_active', true)->orderByDesc('working_days_before')->get();
        $orders = WorkOrder::query()->where('status', 'active')->get();
        $due = [];
        foreach ($orders as $order) {
            foreach ($rules as $rule) {
                $isExpiredReminder = (int) $rule->working_days_before === 0 && $order->end_date->lessThanOrEqualTo($today);
                if ($isExpiredReminder || $this->reminderTriggerDate($order->end_date, (int) $rule->working_days_before)->equalTo($today)) {
                    $due[] = [$order, $rule];
                }
            }
        }
        return $due;
    }
}
