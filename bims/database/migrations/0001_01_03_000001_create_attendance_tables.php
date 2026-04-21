<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100)->nullable();
            $table->time('shift_in');
            $table->time('shift_out');
            $table->boolean('is_overnight')->default(false)->comment('shift crosses midnight');
            $table->boolean('is_archived')->default(false);
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            $table->timestamps();
        });

        Schema::create('attendance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('log_date');
            $table->dateTime('clock_in');
            $table->dateTime('clock_out')->nullable();
            // Duration stored as integer minutes — eliminates the "8.30" string bug
            $table->unsignedInteger('total_minutes')->nullable()
                  ->comment('Computed on clock_out. Exact integer minutes worked.');
            $table->enum('reason', ['Shift', 'Lunch', 'Break'])->default('Shift');
            $table->enum('status_in',  ['In Time', 'Late In', 'Lunch In', 'Break In', 'Ok'])->nullable();
            $table->enum('status_out', ['On Time', 'Early Out', 'Lunch Out', 'Break Out', 'Ok'])->nullable();
            $table->string('comment', 500)->nullable();
            $table->string('logged_by')->nullable();
            $table->boolean('is_approved')->default(false);
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('payroll_run_id')->nullable(); // set FK after payroll_runs exists
            $table->timestamps();

            $table->index(['employee_id', 'log_date']);
            $table->index('log_date');
        });

        Schema::create('incident_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('ref_type', 50)->nullable()->comment('attendance, sale, leave');
            $table->unsignedBigInteger('ref_id')->nullable();
            $table->text('description');
            $table->string('logged_by')->nullable();
            $table->string('admin_ref', 100)->nullable();
            $table->timestamps();

            $table->index(['ref_type', 'ref_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incident_reports');
        Schema::dropIfExists('attendance_logs');
        Schema::dropIfExists('schedules');
    }
};
