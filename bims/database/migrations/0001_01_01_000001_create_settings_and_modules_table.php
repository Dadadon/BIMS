<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('timezone')->default('UTC');
            $table->tinyInteger('time_format')->default(1)->comment('1=12h 2=24h');
            $table->boolean('clock_comment')->default(false);
            $table->boolean('rfid_enabled')->default(false);
            $table->text('ip_whitelist')->nullable();
            $table->string('theme', 50)->default('default');
            $table->string('logo_path')->nullable();
            $table->json('overtime_config')->nullable();
            $table->unsignedInteger('max_attachment_mb')->default(10);
        });

        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('key', 50)->unique();
            $table->string('label');
            $table->text('description')->nullable();
            $table->boolean('is_core')->default(false);
            $table->boolean('is_enabled')->default(true);
            $table->json('settings')->nullable();
        });

        // Seed core module registry
        DB::table('modules')->insert([
            ['key' => 'hr',          'label' => 'HR & Employees',  'is_core' => true,  'is_enabled' => true],
            ['key' => 'attendance',  'label' => 'Attendance',       'is_core' => true,  'is_enabled' => true],
            ['key' => 'leaves',      'label' => 'Leaves',           'is_core' => false, 'is_enabled' => true],
            ['key' => 'sales',       'label' => 'Sales',            'is_core' => false, 'is_enabled' => true],
            ['key' => 'payroll',     'label' => 'Payroll',          'is_core' => false, 'is_enabled' => true],
            ['key' => 'performance', 'label' => 'Performance',      'is_core' => false, 'is_enabled' => true],
            ['key' => 'tasks',       'label' => 'Tasks',            'is_core' => false, 'is_enabled' => true],
            ['key' => 'chat',        'label' => 'Chat',             'is_core' => false, 'is_enabled' => true],
        ]);

        // Seed default settings row
        DB::table('settings')->insert([
            'company_name'    => 'My Company',
            'timezone'        => 'UTC',
            'time_format'     => 1,
            'clock_comment'   => false,
            'rfid_enabled'    => false,
            'overtime_config' => json_encode([
                'daily_threshold_hours'  => 8,
                'weekly_threshold_hours' => 40,
                'multiplier'             => 1.5,
            ]),
            'max_attachment_mb' => 10,
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('modules');
        Schema::dropIfExists('settings');
    }
};
