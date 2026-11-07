<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 100)->index();
            $table->string('subject_type', 150)->nullable();
            $table->string('subject_id', 80)->nullable();
            $table->string('status', 20)->default('success')->index();
            $table->uuid('request_id')->nullable()->index();
            $table->json('context')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
            $table->index(['subject_type', 'subject_id']);
        });

        Schema::create('backup_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('archive_name', 255)->nullable();
            $table->char('sha256', 64)->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->string('status', 20)->index();
            $table->string('failure_code', 80)->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backup_logs');
        Schema::dropIfExists('audit_logs');
    }
};
