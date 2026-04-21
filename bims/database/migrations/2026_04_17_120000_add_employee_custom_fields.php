<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_field_definitions', function (Blueprint $table) {
            $table->id();
            $table->string('key', 80)->unique();
            $table->string('label', 150);
            $table->enum('field_type', ['text', 'number', 'date', 'select', 'textarea', 'checkbox'])
                  ->default('text');
            $table->json('options')->nullable();
            $table->boolean('is_required')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('show_on_create')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->json('metadata')->nullable()->after('avatar');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_field_definitions');
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('metadata');
        });
    }
};
