<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120)->unique();
            $table->string('code', 40)->unique();
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('activities', function (Blueprint $table): void {
            $table->id();
            $table->string('reference_code', 80)->unique();
            $table->string('title', 200);
            $table->foreignId('activity_category_id')->constrained()->restrictOnDelete();
            $table->date('activity_date')->index();
            $table->unsignedSmallInteger('activity_year')->index();
            $table->string('unit', 120)->nullable();
            $table->string('report_status', 30)->default('belum_dikirim')->index();
            $table->date('report_submitted_at')->nullable();
            $table->text('administrative_notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['activity_category_id', 'activity_date']);
            $table->index(['report_status', 'activity_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
        Schema::dropIfExists('activity_categories');
    }
};
