<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpi_definitions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('module_key', 50);    // 'attendance', 'sales'
            $table->string('metric', 100);        // 'late_in_count', 'total_agent_points', 'attendance_rate'
            $table->decimal('target_value', 12, 2)->nullable();
            $table->string('unit', 20)->nullable();   // '%', 'points', 'count'
            $table->enum('direction', ['higher_is_better', 'lower_is_better'])->default('higher_is_better');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('kpi_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('kpi_id')->constrained('kpi_definitions')->cascadeOnDelete();
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('value', 12, 2);
            $table->decimal('score', 5, 2)->nullable()->comment('Normalized 0-100');
            $table->timestamp('computed_at')->useCurrent();

            $table->index(['employee_id', 'kpi_id', 'period_start']);
        });

        // Seed default KPIs
        DB::table('kpi_definitions')->insert([
            [
                'name'         => 'Punctuality',
                'module_key'   => 'attendance',
                'metric'       => 'late_in_count',
                'target_value' => 0,
                'unit'         => 'count',
                'direction'    => 'lower_is_better',
                'created_at'   => now(), 'updated_at' => now(),
            ],
            [
                'name'         => 'Attendance Rate',
                'module_key'   => 'attendance',
                'metric'       => 'attendance_rate',
                'target_value' => 100,
                'unit'         => '%',
                'direction'    => 'higher_is_better',
                'created_at'   => now(), 'updated_at' => now(),
            ],
            [
                'name'         => 'Sales Volume',
                'module_key'   => 'sales',
                'metric'       => 'total_agent_points',
                'target_value' => 1000,
                'unit'         => 'points',
                'direction'    => 'higher_is_better',
                'created_at'   => now(), 'updated_at' => now(),
            ],
            [
                'name'         => 'Sales Count',
                'module_key'   => 'sales',
                'metric'       => 'sale_count',
                'target_value' => 20,
                'unit'         => 'count',
                'direction'    => 'higher_is_better',
                'created_at'   => now(), 'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_snapshots');
        Schema::dropIfExists('kpi_definitions');
    }
};
