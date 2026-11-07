<?php

namespace Database\Seeders;

use App\Models\ReportSubtype;
use App\Models\ReportType;
use Illuminate\Database\Seeder;

class ReportTypeSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['name' => 'Laporan umum', 'code' => 'UMUM', 'subtype' => 'Subjenis umum', 'subcode' => 'UMUM'],
            ['name' => 'Laporan berkala', 'code' => 'BERKALA', 'subtype' => 'Subjenis berkala', 'subcode' => 'BERKALA'],
            ['name' => 'Laporan lainnya', 'code' => 'LAIN', 'subtype' => 'Subjenis lainnya', 'subcode' => 'LAIN'],
        ] as $item) {
            $type = ReportType::firstOrCreate(
                ['code' => $item['code']],
                ['name' => $item['name'], 'description' => 'Kategori generik untuk pengembangan lokal.'],
            );

            ReportSubtype::firstOrCreate(
                ['report_type_id' => $type->id, 'code' => $item['subcode']],
                ['name' => $item['subtype'], 'description' => 'Subjenis generik untuk pengembangan lokal.'],
            );
        }

        foreach ([
            ['name' => 'Lapinhar', 'code' => 'LAPINHAR'],
            ['name' => 'Lapinsus', 'code' => 'LAPINSUS'],
            ['name' => 'Lapsus', 'code' => 'LAPSUS'],
            ['name' => 'Lapopsin', 'code' => 'LAPOPSIN'],
        ] as $item) {
            ReportType::firstOrCreate(
                ['code' => $item['code']],
                [
                    'name' => $item['name'],
                    'description' => 'Pilihan awal; aktifkan hanya setelah dikonfirmasi digunakan oleh kantor.',
                    'is_active' => false,
                ],
            );
        }
    }
}
