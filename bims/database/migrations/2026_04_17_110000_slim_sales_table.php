<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            // Drop FK constraints before their columns
            $table->dropForeign(['cancel_reason_id']);
            $table->dropForeign(['disconnection_reason_id']);

            $table->dropColumn([
                'portal',
                'vendor',
                'customer_email',
                'customer_address',
                'customer_account_number',
                'order_number',
                'activation_date',
                'cancel_date',
                'cancel_reason_id',
                'disconnection_date',
                'disconnection_reason_id',
                'payment_date',
                'chargeback_date',
                'chargeback_received',
                'actual_comp_amount',
                'actual_chargeback_amount',
                'actual_bonus_amount',
            ]);
        });

        // cancel_reasons is now orphaned — drop it
        Schema::dropIfExists('cancel_reasons');
    }

    public function down(): void
    {
        Schema::create('cancel_reasons', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('label');
            $table->text('description')->nullable();
            $table->boolean('is_voluntary')->nullable();
            $table->boolean('is_controllable')->nullable();
            $table->timestamps();
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->string('portal', 100)->nullable()->after('sale_date');
            $table->string('vendor', 100)->nullable()->after('portal');
            $table->string('customer_email')->nullable()->after('customer_name');
            $table->text('customer_address')->nullable()->after('customer_phone');
            $table->string('customer_account_number', 100)->nullable();
            $table->string('order_number', 100)->nullable()->index();
            $table->date('activation_date')->nullable();
            $table->date('cancel_date')->nullable();
            $table->foreignId('cancel_reason_id')->nullable()->constrained('cancel_reasons')->nullOnDelete();
            $table->date('disconnection_date')->nullable();
            $table->foreignId('disconnection_reason_id')->nullable()->constrained('cancel_reasons')->nullOnDelete();
            $table->date('payment_date')->nullable()->index();
            $table->date('chargeback_date')->nullable();
            $table->boolean('chargeback_received')->default(false);
            $table->decimal('actual_comp_amount', 10, 2)->nullable();
            $table->decimal('actual_chargeback_amount', 10, 2)->nullable();
            $table->decimal('actual_bonus_amount', 10, 2)->nullable();
        });
    }
};
