<?php

namespace Database\Seeders;

use App\Models\ActivityCategory;
use Illuminate\Database\Seeder;

class ActivityCategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['name' => 'Administratif Umum', 'code' => 'ADM', 'description' => 'Kategori generik untuk pengembangan lokal.'],
            ['name' => 'Koordinasi', 'code' => 'KRD', 'description' => 'Kategori generik untuk pengembangan lokal.'],
            ['name' => 'Pelaporan', 'code' => 'LPR', 'description' => 'Kategori generik untuk pengembangan lokal.'],
        ] as $category) {
            ActivityCategory::firstOrCreate(['code' => $category['code']], $category);
        }
    }
}
