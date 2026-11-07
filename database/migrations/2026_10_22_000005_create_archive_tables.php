<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_types', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120)->unique();
            $table->string('code', 40)->unique();
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('report_subtypes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('report_type_id')->constrained()->restrictOnDelete();
            $table->string('name', 120);
            $table->string('code', 40);
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->unique(['report_type_id', 'code']);
            $table->unique(['report_type_id', 'name']);
        });

        Schema::create('archive_documents', function (Blueprint $table): void {
            $table->id();
            $table->string('archive_code', 80)->unique();
            $table->foreignId('report_type_id')->constrained()->restrictOnDelete();
            $table->foreignId('report_subtype_id')->constrained()->restrictOnDelete();
            $table->foreignId('activity_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('title', 200);
            $table->string('document_number', 100)->nullable()->index();
            $table->date('report_date')->index();
            $table->unsignedSmallInteger('report_year')->index();
            $table->string('storage_path', 255);
            $table->string('original_file_name', 255);
            $table->string('mime_type', 150);
            $table->unsignedBigInteger('file_size');
            $table->char('sha256', 64);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('uploaded_at');
            $table->text('administrative_notes')->nullable();
            $table->string('sending_status', 30)->default('belum_dikirim')->index();
            $table->date('sent_to_kejati_at')->nullable();
            $table->string('verification_status', 30)->default('belum_diverifikasi')->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['report_type_id', 'report_subtype_id', 'report_date'], 'archive_doc_type_subtype_date_idx');
            $table->index(['report_type_id', 'report_year']);
            $table->index(['activity_id', 'report_year']);
        });

        Schema::create('archive_document_versions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('archive_document_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('version_number');
            $table->string('storage_path', 255)->unique();
            $table->string('original_file_name', 255);
            $table->string('mime_type', 150);
            $table->unsignedBigInteger('file_size');
            $table->char('sha256', 64);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('uploaded_at');
            $table->timestamps();
            $table->unique(['archive_document_id', 'version_number'], 'archive_doc_version_number_uq');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('archive_document_versions');
        Schema::dropIfExists('archive_documents');
        Schema::dropIfExists('report_subtypes');
        Schema::dropIfExists('report_types');
    }
};
