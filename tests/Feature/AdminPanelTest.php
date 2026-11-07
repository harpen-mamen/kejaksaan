<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_be_created_with_the_user_management_fields(): void
    {
        $user = User::query()->create([
            'name' => 'Pengguna Uji',
            'email' => 'pengguna-uji@example.test',
            'password' => 'KataSandiUji!2026',
            'is_active' => true,
        ]);

        $this->assertTrue($user->is_active);
        $this->assertTrue(Hash::check('KataSandiUji!2026', $user->password));
    }

    public function test_login_panel_is_available_and_dashboard_renders_for_authorized_user(): void
    {
        $this->get('/admin/login')->assertOk();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $user = User::factory()->create(['is_active' => true]);
        $role = Role::query()->where('slug', 'administrator-sistem')->firstOrFail();
        $user->roles()->sync([$role->id]);

        $order = app(\App\Services\WorkOrderManager::class)->create([
            'number' => 'DEV/PANEL/001',
            'work_order_type_id' => \App\Models\WorkOrderType::query()->where('code', 'tugas')->value('id'),
            'start_date' => '2026-10-22',
        ], $user);

        $this->actingAs($user)->get('/admin')->assertOk();
        $this->get('/admin/archive-documents')->assertOk();
        $this->get('/admin/archive-documents/create')->assertOk();
        $this->get('/admin/archive-documents/'.$this->reportDocumentId())->assertOk();
        $this->get('/admin/work-orders')->assertOk();
        $this->get('/admin/work-order-reminders')->assertOk();
        foreach ([
            '/admin/activities', '/admin/activity-categories', '/admin/audit-logs', '/admin/backup-logs',
            '/admin/holidays', '/admin/reminder-rules', '/admin/report-types', '/admin/report-subtypes',
            '/admin/roles', '/admin/users', '/admin/work-order-types',
        ] as $path) {
            $this->get($path)->assertOk();
        }
        $this->get(\App\Filament\Resources\WorkOrders\WorkOrderResource::getUrl('view', ['record' => $order->getKey()]))->assertOk();
        foreach ([
            '/admin/work-orders/create', '/admin/activities/create',
            '/admin/activity-categories/create', '/admin/holidays/create', '/admin/reminder-rules/create',
            '/admin/report-types/create', '/admin/report-subtypes/create', '/admin/roles/create',
            '/admin/users/create', '/admin/work-order-types/create',
        ] as $path) {
            $this->get($path)->assertOk();
        }
    }

    private function reportDocumentId(): int
    {
        $type = \App\Models\ReportType::query()->firstOrFail();
        $subtypeId = \App\Models\ReportSubtype::query()->where('report_type_id', $type->id)->value('id');

        return \App\Models\ArchiveDocument::query()->create([
            'archive_code' => 'DEV/PANEL/REPORT/001',
            'report_type_id' => $type->id,
            'report_subtype_id' => $subtypeId,
            'title' => 'Laporan fiktif untuk panel',
            'report_date' => '2026-10-22',
            'report_year' => 2026,
            'storage_path' => 'documents/00000000-0000-4000-8000-000000000001.txt',
            'original_file_name' => 'laporan.txt',
            'mime_type' => 'text/plain',
            'file_size' => 1,
            'sha256' => str_repeat('a', 64),
            'uploaded_at' => now(),
            'created_by' => auth()->id(),
            'uploaded_by' => auth()->id(),
        ])->getKey();
    }
}
