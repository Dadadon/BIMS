<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->foreignId('leader_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('description', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('team_id')->nullable()->after('leave_group_id')
                  ->constrained('teams')->nullOnDelete();
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('team_id')->nullable()->after('employee_id')
                  ->constrained('teams')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sales', fn(Blueprint $t) => $t->dropForeignIdFor(\App\Models\Sales\Team::class, 'team_id'));
        Schema::table('employees', fn(Blueprint $t) => $t->dropForeignIdFor(\App\Models\HR\Team::class, 'team_id'));
        Schema::dropIfExists('teams');
    }
};
