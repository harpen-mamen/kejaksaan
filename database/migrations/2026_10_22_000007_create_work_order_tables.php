<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_order_types', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120)->unique();
            $table->string('code', 50)->unique();
            $table->unsignedSmallInteger('default_duration_days');
            $table->string('duration_basis', 20)->default('calendar_days');
            $table->boolean('start_date_is_day_one')->default(false);
            $table->boolean('is_active')->default(true)->index();
            $table->string('calculation_note')->nullable();
            $table->timestamps();
        });

        Schema::create('holidays', function (Blueprint $table): void {
            $table->id();
            $table->date('date')->unique();
            $table->string('name', 160);
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('reminder_rules', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 100)->unique();
            $table->unsignedSmallInteger('working_days_before')->unique();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('work_orders', function (Blueprint $table): void {
            $table->id();
            $table->string('number', 100)->unique();
            $table->foreignId('work_order_type_id')->constrained()->restrictOnDelete();
            $table->date('start_date')->index();
            $table->unsignedSmallInteger('duration_days');
            $table->string('duration_basis', 20);
            $table->boolean('start_date_is_day_one')->default(false);
            $table->date('end_date')->index();
            $table->string('status', 20)->default('active')->index();
            $table->foreignId('responsible_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('closed_at')->nullable();
            $table->text('administrative_notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['status', 'end_date']);
        });

        Schema::create('work_order_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('work_order_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event_type', 40)->index();
            $table->date('previous_end_date')->nullable();
            $table->date('new_end_date')->nullable();
            $table->unsignedSmallInteger('previous_duration_days')->nullable();
            $table->unsignedSmallInteger('new_duration_days')->nullable();
            $table->string('approval_reference', 150)->nullable();
            $table->text('reason')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });

        Schema::create('work_order_reminders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('work_order_id')->constrained()->restrictOnDelete();
            $table->foreignId('reminder_rule_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('end_date_snapshot');
            $table->date('triggered_for');
            $table->timestamp('read_at')->nullable();
            $table->timestamp('invalidated_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['work_order_id', 'reminder_rule_id', 'user_id', 'end_date_snapshot'], 'work_order_reminder_idempotency');
            $table->index(['user_id', 'read_at', 'invalidated_at']);
            $table->index('triggered_for');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_order_reminders');
        Schema::dropIfExists('work_order_events');
        Schema::dropIfExists('work_orders');
        Schema::dropIfExists('reminder_rules');
        Schema::dropIfExists('holidays');
        Schema::dropIfExists('work_order_types');
    }
};
