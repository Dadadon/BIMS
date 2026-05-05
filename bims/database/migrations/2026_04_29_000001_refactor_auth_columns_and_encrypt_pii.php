<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── users: rename provider columns, add default, add team_id ─────────
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('provider', 'auth_provider');
            $table->renameColumn('provider_id', 'external_id');
        });

        Schema::table('users', function (Blueprint $table) {
            // Backfill NULLs then set DB default
            $table->string('auth_provider')->default('local')->change();
            $table->foreignId('team_id')
                  ->nullable()
                  ->after('employee_id')
                  ->constrained('teams')
                  ->nullOnDelete();

            $table->index(['auth_provider', 'external_id'], 'users_auth_provider_external_id_index');
        });

        // Existing rows with NULL auth_provider → 'local'
        DB::table('users')->whereNull('auth_provider')->update(['auth_provider' => 'local']);

        // ── employees: widen PII columns to TEXT for encrypted values ─────────
        Schema::table('employees', function (Blueprint $table) {
            $table->text('national_id')->nullable()->change();
            $table->text('sip_password')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('national_id', 100)->nullable()->change();
            $table->string('sip_password')->nullable()->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_auth_provider_external_id_index');
            $table->dropForeign(['team_id']);
            $table->dropColumn('team_id');
            $table->string('auth_provider')->nullable()->default(null)->change();
            $table->renameColumn('auth_provider', 'provider');
            $table->renameColumn('external_id', 'provider_id');
        });
    }
};
