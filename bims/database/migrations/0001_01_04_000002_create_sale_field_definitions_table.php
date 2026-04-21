<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_field_definitions', function (Blueprint $table) {
            $table->id();
            $table->string('key', 80)->unique();
            $table->string('label', 150);
            $table->enum('field_type', ['text', 'number', 'date', 'select', 'textarea', 'checkbox'])
                  ->default('text');
            $table->json('options')->nullable()->comment('For select fields: [{"value":"x","label":"X"}]');
            $table->foreignId('sale_type_id')->nullable()->constrained()->nullOnDelete()
                  ->comment('Null = applies to all sale types');
            $table->boolean('is_required')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('show_in_table')->default(false)->comment('Show as a column in the sales list');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_field_definitions');
    }
};
