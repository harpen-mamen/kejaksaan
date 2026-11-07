<?php

namespace Tests\Feature;

use App\Models\Holiday;
use App\Models\Permission;
use App\Models\ReminderRule;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\WorkOrderType;
use App\Services\WorkOrderManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class WorkOrderWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.timezone' => 'Asia/Makassar']);
        $this->seed(\Database\Seeders\AccessControlSeeder::class);
        $this->seed(\Database\Seeders\WorkOrderSetupSeeder::class);
    }

    private function viewer(): User
    {
        $role = Role::query()->create(['name' => uniqid('Viewer '), 'slug' => uniqid('viewer-')]);
        $permissions = Permission::query()
            ->whereIn('name', ['work_orders.view', 'work_orders.manage'])
            ->pluck('id');
        $role->permissions()->sync($permissions);
        $user = User::factory()->create(['is_active' => true]);
        $user->roles()->sync([$role->id]);
        return $user;
    }

    private function createOrder(User $actor, string $startDate = '2026-05-18', int $days = 7): WorkOrder
    {
        $type = WorkOrderType::query()->where('code', 'tugas')->firstOrFail();
        return app(WorkOrderManager::class)->create(['number' => uniqid('DEV/'), 'work_order_type_id' => $type->id, 'start_date' => $startDate, 'duration_days' => $days], $actor);
    }

    public function test_work_order_date_is_calculated_from_snapshotted_config_and_history_is_kept(): void
    {
        $actor = $this->viewer();
        $order = $this->createOrder($actor);
        $this->assertSame('2026-05-25', $order->end_date->toDateString());
        $this->assertDatabaseHas('work_order_events', ['work_order_id' => $order->id, 'event_type' => 'created']);

        $past = $this->createOrder($actor, '2020-01-01', 2);
        $this->assertSame('berakhir', app(\App\Services\WorkOrderCalendarService::class)->status($past, '2020-01-04'));
        $this->assertDatabaseHas('work_orders', ['id' => $past->id, 'status' => 'active']);
    }

    public function test_holidays_are_excluded_and_work_order_date_change_invalidates_unread_reminder(): void
    {
        Holiday::query()->create(['date' => '2026-05-22', 'name' => 'Libur uji']);
        $actor = $this->viewer();
        $order = $this->createOrder($actor);
        $oneDay = ReminderRule::query()->where('working_days_before', 1)->firstOrFail();
        $this->assertSame('2026-05-21', app(\App\Services\WorkOrderCalendarService::class)->reminderTriggerDate($order->end_date, 1)->toDateString());
        $reminder = $order->reminders()->create(['reminder_rule_id' => $oneDay->id, 'user_id' => $actor->id, 'end_date_snapshot' => $order->end_date, 'triggered_for' => '2026-05-21']);

        app(WorkOrderManager::class)->update($order, ['number' => $order->number, 'work_order_type_id' => $order->work_order_type_id, 'start_date' => '2026-05-19', 'duration_days' => 7], $actor);

        $this->assertNotNull($reminder->fresh()->invalidated_at);
        $this->assertSame('2026-05-26', $order->fresh()->end_date->toDateString());
        $this->assertDatabaseHas('work_order_reminders', ['id' => $reminder->id]);
    }

    public function test_reminder_command_is_repeatable_and_creates_ten_seven_and_one_day_alerts_once(): void
    {
        $actor = $this->viewer();
        $order = $this->createOrder($actor);
        $this->assertSame('2026-05-25', $order->end_date->toDateString());

        foreach (['2026-05-11' => 10, '2026-05-14' => 7, '2026-05-22' => 1, '2026-05-25' => 0] as $date => $offset) {
            $this->assertSame(0, Artisan::call('work-orders:generate-reminders', ['--date' => $date]));
            $this->assertSame(0, Artisan::call('work-orders:generate-reminders', ['--date' => $date]));
            $rule = ReminderRule::query()->where('working_days_before', $offset)->firstOrFail();
            $this->assertSame(1, $order->reminders()->where('reminder_rule_id', $rule->id)->whereNull('invalidated_at')->count());
        }
    }
}
