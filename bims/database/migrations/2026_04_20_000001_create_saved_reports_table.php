<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saved_reports', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->string('data_source', 50);
            $table->json('columns');
            $table->json('filters')->nullable();
            $table->string('group_by', 100)->nullable();
            $table->string('aggregate_fn', 20)->nullable();
            $table->string('aggregate_field', 100)->nullable();
            $table->string('chart_type', 20)->default('table');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_reports');
    }
};
