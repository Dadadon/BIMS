<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->nullable()->constrained()->nullOnDelete()
                  ->comment('NULL = applies to all active employees');
            $table->enum('type', ['addition', 'deduction']);
            $table->enum('category', [
                'allowance', 'bonus', 'loan_repayment',
                'cash_advance', 'absence', 'late', 'other',
            ]);
            $table->string('description', 150);
            $table->enum('amount_type', ['fixed', 'percentage'])->default('fixed');
            $table->decimal('amount', 10, 2);
            $table->boolean('is_recurring')->default(true)
                  ->comment('true = every period; false = one-time (auto-deactivates after first use)');
            $table->date('effective_date')->nullable();
            $table->date('expires_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['employee_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_adjustments');
    }
};
