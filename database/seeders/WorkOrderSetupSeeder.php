<?php

namespace Database\Seeders;

use App\Models\ReminderRule;
use App\Models\WorkOrderType;
use Illuminate\Database\Seeder;

class WorkOrderSetupSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['name' => 'Surat Perintah Tugas', 'code' => 'tugas', 'default_duration_days' => 7],
            ['name' => 'Surat Perintah Opsin', 'code' => 'opsin', 'default_duration_days' => 30],
        ] as $type) {
            WorkOrderType::firstOrCreate(
                ['code' => $type['code']],
                [
                    ...$type,
                    'duration_basis' => 'calendar_days',
                    'start_date_is_day_one' => false,
                    'calculation_note' => 'Default pengembangan sementara: hari kalender; tanggal mulai tidak dihitung sebagai hari pertama. Konfirmasi aturan resmi sebelum data nyata.',
                ],
            );
        }

        foreach ([10 => '10 hari kerja sebelum tenggat', 7 => '7 hari kerja sebelum tenggat', 1 => '1 hari kerja sebelum tenggat', 0 => 'Tenggat tiba'] as $days => $label) {
            ReminderRule::firstOrCreate(['working_days_before' => $days], ['name' => $label, 'is_active' => true]);
        }
    }
}
