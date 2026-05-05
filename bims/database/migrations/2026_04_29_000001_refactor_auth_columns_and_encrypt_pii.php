<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── users: auth_provider column ───────────────────────────────────────
        // Rename from 'provider' if it exists, otherwise add fresh
        if (Schema::hasColumn('users', 'provider')) {
            Schema::table('users', function (Blueprint $table) {
                $table->renameColumn('provider', 'auth_provider');
            });
        }

        if (! Schema::hasColumn('users', 'auth_provider')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('auth_provider')->nullable()->after('email_verified_at');
            });
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('auth_provider')->default('local')->change();
        });

        DB::table('users')->whereNull('auth_provider')->update(['auth_provider' => 'local']);

        // ── users: external_id column ─────────────────────────────────────────
        if (Schema::hasColumn('users', 'provider_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->renameColumn('provider_id', 'external_id');
            });
        }

        if (! Schema::hasColumn('users', 'external_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('external_id')->nullable()->after('auth_provider');
            });
        }

        // ── users: team_id + index ────────────────────────────────────────────
        if (! Schema::hasColumn('users', 'team_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('team_id')
                      ->nullable()
                      ->after('employee_id')
                      ->constrained('teams')
                      ->nullOnDelete();
            });
        }

        // Composite index for provider lookups (add only if not already there)
        $indexes = collect(DB::select("SHOW INDEX FROM users WHERE Key_name = 'users_auth_provider_external_id_index'"));
        if ($indexes->isEmpty()) {
            Schema::table('users', function (Blueprint $table) {
                $table->index(['auth_provider', 'external_id'], 'users_auth_provider_external_id_index');
            });
        }

        // ── employees: widen PII columns to TEXT for encrypted values ─────────
        Schema::table('employees', function (Blueprint $table) {
            $table->text('national_id')->nullable()->change();
            $table->text('sip_password')->nullable()->change();
        });

        // ── settings: ensure external_auth_domains column exists ──────────────
        if (! Schema::hasColumn('settings', 'external_auth_domains')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->json('external_auth_domains')->nullable()->after('ip_whitelist');
            });
        }
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
            $table->renameColumn('auth_provider', 'provider');
            $table->renameColumn('external_id', 'provider_id');
        });
    }
};
