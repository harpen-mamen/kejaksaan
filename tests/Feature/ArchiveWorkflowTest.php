<?php

namespace Tests\Feature;

use App\Models\ArchiveDocument;
use App\Models\AuditLog;
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
use Tests\TestCase;

class ArchiveWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('archive_private');
        $this->seed(\Database\Seeders\AccessControlSeeder::class);
        config(['app.timezone' => 'Asia/Makassar']);
    }

    private function typeAndSubtype(): array
    {
        $type = ReportType::query()->create(['name' => 'Laporan Fiktif', 'code' => 'dev-fiktif', 'is_active' => true]);
        $subtype = ReportSubtype::query()->create(['report_type_id' => $type->id, 'name' => 'Subjenis Fiktif', 'code' => 'sub-fiktif', 'is_active' => true]);
        return [$type, $subtype];
    }

    private function actorWithPermissions(array $permissions): User
    {
        $role = Role::query()->create(['name' => uniqid('Role uji '), 'slug' => uniqid('uji-'), 'is_system' => false]);
        $ids = collect($permissions)->map(fn (string $name) => Permission::query()->where('name', $name)->value('id'))->filter()->all();
        $role->permissions()->sync($ids);
        $user = User::factory()->create(['is_active' => true]);
        $user->roles()->sync([$role->id]);
        return $user;
    }

    public function test_new_archive_and_replacement_version_never_overwrite_previous_file(): void
    {
        [$type, $subtype] = $this->typeAndSubtype();
        $user = $this->actorWithPermissions(['archives.view', 'archives.download', 'archives.upload', 'archives.update']);
        $manager = app(ArchiveDocumentManager::class);
        $data = ['archive_code' => 'DEV-001', 'report_type_id' => $type->id, 'report_subtype_id' => $subtype->id, 'title' => 'Dokumen fiktif', 'document_number' => 'R-001', 'report_date' => '2024-02-29', 'sending_status' => 'belum_dikirim', 'verification_status' => 'belum_diverifikasi'];
        $document = $manager->create($data, UploadedFile::fake()->createWithContent('lama.txt', 'isi lama'), $user);
        $oldPath = $document->storage_path;
        $oldVersion = $document->versions()->firstOrFail();

        $manager->update($document, $data, UploadedFile::fake()->createWithContent('baru.txt', 'isi baru'), $user);

        $document->refresh();
        $this->assertNotSame($oldPath, $document->storage_path);
        $this->assertSame(2, $document->versions()->count());
        Storage::disk('archive_private')->assertExists($oldPath);
        Storage::disk('archive_private')->assertExists($document->storage_path);
        $this->actingAs($user)->get(route('archives.versions.download', [$document, $oldVersion]))->assertOk();
        $this->actingAs($user)->get(route('archives.download', $document))->assertOk();
    }

    public function test_subtype_must_belong_to_selected_report_type(): void
    {
        [$type] = $this->typeAndSubtype();
        $otherType = ReportType::query()->create(['name' => 'Jenis lain', 'code' => 'jenis-lain', 'is_active' => true]);
        $wrongSubtype = ReportSubtype::query()->create(['report_type_id' => $otherType->id, 'name' => 'Subjenis lain', 'code' => 'lain', 'is_active' => true]);
        $user = $this->actorWithPermissions(['archives.upload']);
        $this->expectException(ValidationException::class);
        app(ArchiveDocumentManager::class)->create(['archive_code' => 'DEV-002', 'report_type_id' => $type->id, 'report_subtype_id' => $wrongSubtype->id, 'title' => 'Tidak sah', 'report_date' => '2024-01-01'], UploadedFile::fake()->createWithContent('x.txt', 'x'), $user);
    }

    public function test_reader_without_download_permission_cannot_download_or_delete_archive(): void
    {
        [$type, $subtype] = $this->typeAndSubtype();
        $owner = $this->actorWithPermissions(['archives.upload', 'archives.download', 'archives.view']);
        $document = app(ArchiveDocumentManager::class)->create(['archive_code' => 'DEV-003', 'report_type_id' => $type->id, 'report_subtype_id' => $subtype->id, 'title' => 'Arsip', 'report_date' => '2024-01-01'], UploadedFile::fake()->createWithContent('data.txt', 'data'), $owner);
        $reader = $this->actorWithPermissions(['archives.view']);

        $this->actingAs($reader)->get(route('archives.download', $document))->assertForbidden();
        $this->assertFalse($reader->can('delete', $document));
        Storage::disk('archive_private')->assertExists($document->storage_path);
    }

    public function test_soft_deleted_metadata_does_not_delete_private_file(): void
    {
        [$type, $subtype] = $this->typeAndSubtype();
        $user = $this->actorWithPermissions(['archives.upload', 'archives.delete']);
        $document = app(ArchiveDocumentManager::class)->create(['archive_code' => 'DEV-004', 'report_type_id' => $type->id, 'report_subtype_id' => $subtype->id, 'title' => 'Arsip', 'report_date' => '2024-01-01'], UploadedFile::fake()->createWithContent('data.txt', 'data'), $user);
        $path = $document->storage_path;
        $document->delete();
        Storage::disk('archive_private')->assertExists($path);
        $this->assertTrue(AuditLog::query()->where('action', 'archive.deleted')->where('subject_type', ArchiveDocument::class)->where('subject_id', $document->id)->exists());
    }
}
