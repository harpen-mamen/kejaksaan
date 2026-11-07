<?php

namespace Tests\Unit;

use App\Models\Holiday;
use App\Models\WorkOrder;
use App\Services\WorkOrderCalendarService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkOrderCalendarServiceTest extends TestCase
{
    use RefreshDatabase;

    private WorkOrderCalendarService $calendar;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.timezone' => 'Asia/Makassar']);
        $this->calendar = app(WorkOrderCalendarService::class);
    }

    public function test_calendar_days_support_inclusive_or_exclusive_start_date(): void
    {
        $this->assertSame('2024-03-01', $this->calendar->calculateEndDate('2024-02-27', 3)->toDateString());
        $this->assertSame('2024-02-29', $this->calendar->calculateEndDate('2024-02-27', 3, 'calendar_days', true)->toDateString());
        $this->assertSame('2025-01-02', $this->calendar->calculateEndDate('2024-12-31', 2)->toDateString());
        $this->assertSame('2024-04-06', $this->calendar->calculateEndDate('2024-04-05', 1)->toDateString());
    }

    public function test_working_day_duration_skips_weekends_and_consecutive_holidays(): void
    {
        Holiday::query()->create(['date' => '2024-04-08', 'name' => 'Libur fiktif 1']);
        Holiday::query()->create(['date' => '2024-04-09', 'name' => 'Libur fiktif 2']);

        $this->assertSame('2024-04-10', $this->calendar->calculateEndDate('2024-04-05', 1, 'working_days')->toDateString());
        $this->assertSame('2024-04-11', $this->calendar->calculateEndDate('2024-04-05', 2, 'working_days')->toDateString());
        $this->assertFalse($this->calendar->isWorkingDay('2024-04-06'));
        $this->assertFalse($this->calendar->isWorkingDay('2024-04-07'));
        $this->assertFalse($this->calendar->isWorkingDay('2024-04-08'));
        $this->assertSame(1, $this->calendar->workingDaysBetween('2024-04-05', '2024-04-10'));
    }

    public function test_reminder_offsets_skip_weekends_holidays_month_boundaries_and_leap_day(): void
    {
        Holiday::query()->create(['date' => '2024-03-01', 'name' => 'Libur fiktif']);
        $this->assertSame('2024-02-29', $this->calendar->reminderTriggerDate('2024-03-04', 1)->toDateString());
        $this->assertSame('2024-03-04', $this->calendar->reminderTriggerDate('2024-03-04', 0)->toDateString());
        $this->assertSame('2024-03-04', $this->calendar->calculateEndDate('2024-02-27', 3, 'working_days')->toDateString());
    }

    public function test_reminders_due_are_calculated_for_ten_seven_and_one_working_days(): void
    {
        $this->seed(\Database\Seeders\WorkOrderSetupSeeder::class);
        $type = \App\Models\WorkOrderType::query()->firstOrFail();
        $record = WorkOrder::query()->create([
            'number' => 'DEV/2026/01', 'work_order_type_id' => $type->id, 'start_date' => '2026-05-18',
            'duration_days' => 7, 'duration_basis' => 'calendar_days', 'start_date_is_day_one' => false,
            'end_date' => '2026-05-25', 'status' => 'active',
        ]);

        foreach (['2026-05-11' => 10, '2026-05-14' => 7, '2026-05-22' => 1, '2026-05-25' => 0] as $date => $offset) {
            $due = $this->calendar->remindersDueOn($date);
            $this->assertTrue(collect($due)->contains(fn (array $item) => $item[0]->is($record) && $item[1]->working_days_before === $offset));
        }
    }
}
