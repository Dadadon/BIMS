<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('password')->nullable()->change();
            $table->string('provider')->nullable()->after('email_verified_at');
            $table->string('provider_id')->nullable()->after('provider');
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->json('external_auth_domains')->nullable()->after('ip_whitelist');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('external_auth_domains');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['provider', 'provider_id']);
            $table->string('password')->nullable(false)->change();
        });
    }
};
