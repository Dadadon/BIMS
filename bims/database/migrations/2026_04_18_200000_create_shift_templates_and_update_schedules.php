<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shift_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->time('shift_in');
            $table->time('shift_out');
            $table->boolean('is_overnight')->default(false);
            $table->unsignedSmallInteger('break_minutes')->default(0);
            $table->string('color', 7)->default('#6366f1');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('schedules', function (Blueprint $table) {
            $table->foreignId('shift_template_id')
                  ->nullable()->after('employee_id')
                  ->constrained('shift_templates')->nullOnDelete();
            $table->unsignedSmallInteger('break_minutes')->default(0)->after('is_overnight');
            $table->json('days_of_week')->nullable()->after('break_minutes')
                  ->comment('JSON array of ISO day numbers: 1=Mon … 7=Sun. Null = every day.');
        });
    }

    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropForeign(['shift_template_id']);
            $table->dropColumn(['shift_template_id', 'break_minutes', 'days_of_week']);
        });
        Schema::dropIfExists('shift_templates');
    }
};
