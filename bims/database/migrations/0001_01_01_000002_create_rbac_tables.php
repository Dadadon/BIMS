<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->boolean('is_admin')->default(false);
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('module_key', 50);
            $table->string('action', 50);          // view, create, edit, delete, export, run
            $table->unique(['module_key', 'action']);
        });

        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->unique(['role_id', 'permission_id']);
        });

        // Seed roles
        DB::table('roles')->insert([
            ['name' => 'System Admin',      'slug' => 'system_admin',  'is_admin' => true,  'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Manager',           'slug' => 'manager',       'is_admin' => true,  'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Team Lead Level 2', 'slug' => 'team_lead_l2',  'is_admin' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Team Lead Level 1', 'slug' => 'team_lead_l1',  'is_admin' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Employee',          'slug' => 'employee',      'is_admin' => false, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Seed all permissions
        $modules = ['hr', 'attendance', 'leaves', 'sales', 'payroll', 'performance', 'tasks', 'chat', 'users', 'settings', 'reports'];
        $actions = ['view', 'view_team', 'view_all', 'create', 'edit', 'delete', 'export', 'run'];

        $perms = [];
        foreach ($modules as $mod) {
            foreach ($actions as $act) {
                // Only relevant combos
                if ($act === 'run' && $mod !== 'payroll') continue;
                if ($act === 'view_team' && !in_array($mod, ['attendance', 'sales', 'performance', 'payroll', 'leaves'])) continue;
                if ($act === 'view_all'  && !in_array($mod, ['sales', 'reports'])) continue;
                $perms[] = ['module_key' => $mod, 'action' => $act];
            }
        }
        DB::table('permissions')->insert($perms);
    }

    public function down(): void
    {
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
