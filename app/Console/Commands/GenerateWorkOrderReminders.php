<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\AuditLogService;
use App\Services\WorkOrderCalendarService;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;

class GenerateWorkOrderReminders extends Command
{
    protected $signature = 'work-orders:generate-reminders {--date= : Evaluation date (YYYY-MM-DD), defaults to today in app timezone}';
    protected $description = 'Create idempotent in-app reminders for authorized work-order viewers.';

    public function handle(WorkOrderCalendarService $calendar, AuditLogService $audit): int
    {
        $date = $this->option('date') ?: now(config('app.timezone', 'Asia/Makassar'))->toDateString();
        try {
            $created = 0;
            foreach ($calendar->remindersDueOn($date) as [$order, $rule]) {
                $recipients = User::query()->where('is_active', true)->whereHas('roles.permissions', fn ($query) => $query->where('name', 'work_orders.view'))->get();
                foreach ($recipients as $user) {
                    try {
                        $order->reminders()->create([
                            'reminder_rule_id' => $rule->getKey(),
                            'user_id' => $user->getKey(),
                            'end_date_snapshot' => $order->end_date->toDateString(),
                            'triggered_for' => $date,
                            'created_at' => now(config('app.timezone')),
                        ]);
                        $created++;
                    } catch (QueryException $exception) {
                        $message = strtolower($exception->getMessage());
                        if (! str_contains($message, 'unique') && ! str_contains($message, 'duplicate')) {
                            throw $exception;
                        }
                    }
                }
            }
            $audit->record('work_order.reminders_generated', context: ['date' => $date, 'created' => $created]);
            $this->info("Pemeriksaan {$date} selesai; {$created} pengingat baru dibuat.");
            return self::SUCCESS;
        } catch (\Throwable $exception) {
            report($exception);
            $audit->record('work_order.reminders_generation_failed', status: 'failed', context: ['date' => $date, 'error' => class_basename($exception)]);
            $this->error('Pemeriksaan pengingat gagal. Lihat log aplikasi untuk request ID dan rincian aman.');
            return self::FAILURE;
        }
    }
}
