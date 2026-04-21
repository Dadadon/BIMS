<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 20)->unique();
            $table->decimal('rate', 6, 4)->default(0)->comment('e.g. 0.0250 = 2.5%');
            $table->decimal('flat_amount', 10, 2)->nullable();
            $table->enum('applies_to', ['gross', 'net', 'taxable'])->default('gross');
            $table->decimal('income_threshold', 12, 2)->nullable()
                  ->comment('Only applies above this income level');
            $table->boolean('is_employer_contribution')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('pay_periods', function (Blueprint $table) {
            $table->id();
            $table->string('label')->nullable();
            $table->enum('period_type', ['weekly', 'biweekly', 'semimonthly', 'monthly']);
            $table->date('start_date');
            $table->date('end_date');
            $table->date('pay_date');
            $table->enum('status', ['open', 'processing', 'closed'])->default('open');
            $table->timestamps();
        });

        Schema::create('payroll_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pay_period_id')->constrained()->restrictOnDelete();
            $table->foreignId('run_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['draft', 'finalized', 'paid'])->default('draft');
            $table->decimal('total_gross', 14, 2)->default(0.00);
            $table->decimal('total_deductions', 14, 2)->default(0.00);
            $table->decimal('total_net', 14, 2)->default(0.00);
            $table->text('notes')->nullable();
            $table->timestamp('finalized_at')->nullable();
            $table->timestamps();
        });

        Schema::create('payroll_slips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_run_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('total_minutes_worked')->default(0);
            $table->decimal('regular_hours', 8, 2)->default(0.00);
            $table->decimal('overtime_hours', 8, 2)->default(0.00);
            $table->decimal('base_rate', 10, 2);
            $table->decimal('gross_salary', 12, 2)->default(0.00);
            $table->decimal('total_additions', 12, 2)->default(0.00);
            $table->decimal('total_deductions', 12, 2)->default(0.00);
            $table->decimal('total_tax', 12, 2)->default(0.00);
            $table->decimal('commission_earned', 12, 2)->default(0.00);
            $table->decimal('net_pay', 12, 2)->default(0.00);
            $table->timestamps();

            $table->unique(['payroll_run_id', 'employee_id']);
        });

        Schema::create('payroll_line_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_slip_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['addition', 'deduction', 'tax', 'commission']);
            $table->string('description');
            $table->decimal('amount', 12, 2);
            $table->string('source', 50)->nullable()->comment('manual, attendance, sales, tax_config');
            $table->unsignedBigInteger('source_ref_id')->nullable();
            $table->timestamps();
        });

        // Add deferred FKs now that all tables exist
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->foreign('payroll_run_id')->references('id')->on('payroll_runs')->nullOnDelete();
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->foreign('payroll_line_item_id')->references('id')->on('payroll_line_items')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['payroll_line_item_id']);
        });
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->dropForeign(['payroll_run_id']);
        });
        Schema::dropIfExists('payroll_line_items');
        Schema::dropIfExists('payroll_slips');
        Schema::dropIfExists('payroll_runs');
        Schema::dropIfExists('pay_periods');
        Schema::dropIfExists('tax_configurations');
    }
};
