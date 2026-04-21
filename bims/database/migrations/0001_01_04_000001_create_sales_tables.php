<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_types', function (Blueprint $table) {
            $table->id();
            $table->string('product_category');
            $table->string('portal', 100)->nullable();
            $table->string('product_code', 100)->nullable();
            $table->unsignedInteger('total_points')->default(0);
            $table->decimal('points_per_agent', 8, 2)->default(0.00);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('cancel_reasons', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('label');
            $table->text('description')->nullable();
            $table->boolean('is_voluntary')->nullable();
            $table->boolean('is_controllable')->nullable();
            $table->timestamps();
        });

        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->restrictOnDelete();
            $table->foreignId('sale_type_id')->nullable()->constrained()->nullOnDelete();
            $table->date('sale_date');
            $table->string('portal', 100)->nullable();
            $table->string('vendor', 100)->nullable();
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_phone', 50)->nullable();
            $table->text('customer_address')->nullable();
            $table->string('customer_account_number', 100)->nullable();
            $table->string('order_number', 100)->nullable()->index();
            $table->decimal('total_points', 10, 2)->default(0.00);
            $table->decimal('agent_points', 10, 2)->default(0.00);
            $table->string('status', 100)->default('Scheduled')->index();
            $table->date('activation_date')->nullable();
            $table->date('cancel_date')->nullable();
            $table->foreignId('cancel_reason_id')->nullable()->constrained('cancel_reasons')->nullOnDelete();
            $table->date('disconnection_date')->nullable();
            $table->foreignId('disconnection_reason_id')->nullable()->constrained('cancel_reasons')->nullOnDelete();
            $table->date('payment_date')->nullable()->index();
            $table->date('chargeback_date')->nullable();
            $table->boolean('compensation_received')->default(false)->index();
            $table->boolean('chargeback_received')->default(false);
            $table->decimal('actual_comp_amount', 10, 2)->nullable();
            $table->decimal('actual_chargeback_amount', 10, 2)->nullable();
            $table->decimal('actual_bonus_amount', 10, 2)->nullable();
            $table->foreignId('payroll_line_item_id')->nullable(); // FK added after payroll migration
            $table->json('metadata')->nullable()
                  ->comment('Industry-specific fields: installation_date, internet_speed, shipping_carrier, etc.');
            $table->timestamps();

            $table->index(['employee_id', 'sale_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
        Schema::dropIfExists('cancel_reasons');
        Schema::dropIfExists('sale_types');
    }
};
