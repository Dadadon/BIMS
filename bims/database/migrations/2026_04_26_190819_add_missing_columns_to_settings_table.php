<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('currency', 10)->nullable()->after('timezone');
            $table->string('date_format', 20)->nullable()->after('currency');
            $table->string('allowed_ips')->nullable()->after('ip_whitelist');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['currency', 'date_format', 'allowed_ips']);
        });
    }
};
