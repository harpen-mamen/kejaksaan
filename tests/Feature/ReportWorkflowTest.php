<?php

namespace Tests\Feature;

use App\Filament\Resources\ArchiveDocuments\Pages\CreateArchiveDocument;
use App\Filament\Resources\ArchiveDocuments\Pages\ListArchiveDocuments;
use App\Models\ArchiveDocument;
use App\Models\Activity;
use App\Models\ActivityCategory;
use App\Models\Permission;
use App\Models\ReportSubtype;
use App\Models\ReportType;
use App\Models\Role;
use App\Models\User;
use App\Services\ArchiveDocumentManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class ReportWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('archive_private');
        $this->seed(\Database\Seeders\AccessControlSeeder::class);
        config(['app.timezone' => 'Asia/Makassar']);
    }

    private function reportType(string $name, string $code, bool $active = true): ReportType
    {
        return ReportType::query()->create(['name' => $name, 'code' => $code, 'is_active' => $active]);
    }

    private function subtype(ReportType $type, string $name, string $code): ReportSubtype
    {
        return ReportSubtype::query()->create(['report_type_id' => $type->id, 'name' => $name, 'code' => $code, 'is_active' => true]);
    }

    private function userWithPermissions(array $permissions, string $name = 'Petugas Uji'): User
    {
        $role = Role::query()->create(['name' => $name, 'slug' => uniqid('uji-'), 'is_system' => false]);
        $permissionIds = collect($permissions)->map(fn (string $permission) => Permission::query()->where('name', $permission)->value('id'))->filter()->all();
        $role->permissions()->sync($permissionIds);
        $user = User::factory()->create(['name' => $name, 'is_active' => true]);
        $user->roles()->sync([$role->id]);

        return $user;
    }

    private function createReport(User $user, ReportType $type, ?ReportSubtype $subtype, string $number, string $date, string $content = 'data laporan'): ArchiveDocument
    {
        return app(ArchiveDocumentManager::class)->create([
            'archive_code' => $number,
            'report_type_id' => $type->id,
            'report_subtype_id' => $subtype?->id,
            'title' => 'Perihal '.$number,
            'document_number' => 'REF-'.$number,
            'report_date' => $date,
            'verification_status' => 'belum_diverifikasi',
        ], UploadedFile::fake()->createWithContent('laporan-sama.txt', $content), $user);
    }

    public function test_subtype_dropdown_is_scoped_and_cleared_when_report_type_changes(): void
    {
        $firstType = $this->reportType('Lapinhar uji', 'LAPINHAR-UJI');
        $firstSubtype = $this->subtype($firstType, 'Kategori A', 'KAT-A');
        $secondType = $this->reportType('Lapsus uji', 'LAPSUS-UJI');
        $secondSubtype = $this->subtype($secondType, 'Kategori B', 'KAT-B');
        $user = $this->userWithPermissions(['dashboard.view', 'archives.view', 'archives.upload']);

        $this->assertSame([$firstSubtype->id => $firstSubtype->name], ReportSubtype::activeOptionsForType($firstType->id)->all());
        $this->assertSame([$secondSubtype->id => $secondSubtype->name], ReportSubtype::activeOptionsForType($secondType->id)->all());

        Livewire::actingAs($user)
            ->test(CreateArchiveDocument::class)
            ->set('data.report_type_id', $firstType->id)
            ->set('data.report_subtype_id', $firstSubtype->id)
            ->set('data.report_type_id', $secondType->id)
            ->assertSet('data.report_subtype_id', null);
    }

    public function test_report_form_lists_activity_titles_and_excludes_deleted_activities(): void
    {
        $category = ActivityCategory::create(['name' => 'Kategori uji', 'code' => 'UJI']);
        $attributes = ['activity_category_id' => $category->id, 'activity_date' => '2026-10-30', 'activity_year' => 2026];
        $last = Activity::create([...$attributes, 'reference_code' => 'UJI-Z', 'title' => 'Zeta kegiatan']);
        $first = Activity::create([...$attributes, 'reference_code' => 'UJI-A', 'title' => 'Alpha kegiatan']);
        $deleted = Activity::create([...$attributes, 'reference_code' => 'UJI-HAPUS', 'title' => 'Kegiatan dihapus']);
        $deleted->delete();
        $user = $this->userWithPermissions(['dashboard.view', 'archives.view', 'archives.upload']);

        $this->actingAs($user)->get('/admin/archive-documents/create')->assertOk();
        Livewire::actingAs($user)->test(CreateArchiveDocument::class)
            ->assertFormFieldExists('activity_id', fn (\Filament\Forms\Components\Select $field): bool =>
                $field->getOptions() === [$first->id => $first->title, $last->id => $last->title]
            );
    }

    public function test_backend_rejects_subtype_from_another_type_and_inactive_type(): void
    {
        $type = $this->reportType('Jenis aktif', 'AKTIF');
        $otherType = $this->reportType('Jenis lain', 'LAIN');
        $wrongSubtype = $this->subtype($otherType, 'Subjenis salah', 'SALAH');
        $user = $this->userWithPermissions(['archives.upload']);
        $manager = app(ArchiveDocumentManager::class);

        try {
            $manager->create([
                'archive_code' => 'DEV-INVALID-SUBTYPE',
                'report_type_id' => $type->id,
                'report_subtype_id' => $wrongSubtype->id,
                'title' => 'Tidak sah',
                'report_date' => '2026-01-01',
            ], UploadedFile::fake()->createWithContent('salah.txt', 'x'), $user);
            $this->fail('Expected a validation exception for an unrelated subtype.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('report_subtype_id', $exception->errors());
        }

        $inactiveType = $this->reportType('Belum dikonfirmasi', 'BELUM-AKTIF', false);
        try {
            $manager->create([
                'archive_code' => 'DEV-INACTIVE-TYPE',
                'report_type_id' => $inactiveType->id,
                'title' => 'Jenis tidak aktif',
                'report_date' => '2026-01-01',
            ], UploadedFile::fake()->createWithContent('nonaktif.txt', 'x'), $user);
            $this->fail('Expected a validation exception for an inactive report type.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('report_type_id', $exception->errors());
        }
    }

    public function test_search_and_filters_find_only_matching_reports_with_pagination(): void
    {
        $typeA = $this->reportType('Lapinhar', 'LAPINHAR');
        $subtypeA = $this->subtype($typeA, 'Kategori A', 'A');
        $typeB = $this->reportType('Lapsus', 'LAPSUS');
        $subtypeB = $this->subtype($typeB, 'Kategori B', 'B');
        $creatorA = $this->userWithPermissions(['archives.upload'], 'Pembuat A');
        $creatorB = $this->userWithPermissions(['archives.upload'], 'Pembuat B');
        $viewer = $this->userWithPermissions(['dashboard.view', 'archives.view'], 'Pembaca');
        $reportA = $this->createReport($creatorA, $typeA, $subtypeA, 'LAP/001', '2026-01-10');
        $reportB = $this->createReport($creatorB, $typeB, $subtypeB, 'LAP/002', '2026-02-10');
        $reportB->update(['verification_status' => 'terverifikasi']);

        $list = Livewire::actingAs($viewer)->test(ListArchiveDocuments::class);
        $this->assertTrue($list->instance()->getTable()->isPaginated());
        $list->assertCountTableRecords(2)
            ->searchTable('LAP/001')
            ->assertCanSeeTableRecords([$reportA])
            ->assertCanNotSeeTableRecords([$reportB]);

        $this->assertSame([10, 25, 50], $list->instance()->getTable()->getPaginationPageOptions());

        $list->searchTable('Perihal LAP/002')
            ->assertCanSeeTableRecords([$reportB])
            ->assertCanNotSeeTableRecords([$reportA]);

        $list->searchTable(null)->filterTable('classification', ['type_id' => $typeB->id, 'subtype_id' => $subtypeB->id])
            ->assertCanSeeTableRecords([$reportB])
            ->assertCanNotSeeTableRecords([$reportA]);

        $list->removeTableFilters()->filterTable('report_date_range', ['from' => '2026-02-01', 'until' => '2026-02-28'])
            ->assertCanSeeTableRecords([$reportB])
            ->assertCanNotSeeTableRecords([$reportA]);

        $list->removeTableFilters()->filterTable('report_date', ['date' => '2026-01-10'])
            ->assertCanSeeTableRecords([$reportA])
            ->assertCanNotSeeTableRecords([$reportB]);

        $list->removeTableFilters()->filterTable('created_by', $creatorA->id)
            ->assertCanSeeTableRecords([$reportA])
            ->assertCanNotSeeTableRecords([$reportB]);

        $list->removeTableFilters()->filterTable('verification_status', 'terverifikasi')
            ->assertCanSeeTableRecords([$reportB])
            ->assertCanNotSeeTableRecords([$reportA]);
    }

    public function test_user_without_report_view_permission_cannot_open_report_list(): void
    {
        $user = $this->userWithPermissions(['dashboard.view']);

        $this->actingAs($user)->get('/admin/archive-documents')->assertForbidden();
    }

    public function test_only_authorized_user_can_open_report_type_configuration(): void
    {
        $manager = $this->userWithPermissions(['dashboard.view', 'settings.manage'], 'Pengelola Laporan');
        $viewer = $this->userWithPermissions(['dashboard.view', 'archives.view'], 'Pembaca Laporan');

        $this->actingAs($manager)->get('/admin/report-types/create')->assertOk();
        $this->actingAs($viewer)->get('/admin/report-types/create')->assertForbidden();
    }

    public function test_upload_with_same_original_filename_creates_a_new_record_and_private_file(): void
    {
        $type = $this->reportType('Jenis upload', 'UPLOAD');
        $subtype = $this->subtype($type, 'Kategori upload', 'UPLOAD-SUB');
        $user = $this->userWithPermissions(['archives.upload']);

        $first = $this->createReport($user, $type, $subtype, 'DEV-UPLOAD-001', '2026-03-01', 'isi berkas pertama');
        $firstPath = $first->storage_path;
        $second = $this->createReport($user, $type, $subtype, 'DEV-UPLOAD-002', '2026-03-02', 'isi berkas kedua');

        $this->assertNotSame($first->getKey(), $second->getKey());
        $this->assertNotSame($firstPath, $second->storage_path);
        $this->assertSame(2, ArchiveDocument::query()->count());
        $this->assertSame('laporan-sama.txt', $first->original_file_name);
        $this->assertSame('laporan-sama.txt', $second->original_file_name);
        Storage::disk('archive_private')->assertExists($firstPath);
        Storage::disk('archive_private')->assertExists($second->storage_path);
        $this->assertSame('isi berkas pertama', Storage::disk('archive_private')->get($firstPath));
        $this->assertSame('isi berkas kedua', Storage::disk('archive_private')->get($second->storage_path));
    }
}
