<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sale_field_definitions', function (Blueprint $table) {
            $table->boolean('show_on_create')->default(false)->after('show_in_table');
        });
    }

    public function down(): void
    {
        Schema::table('sale_field_definitions', function (Blueprint $table) {
            $table->dropColumn('show_on_create');
        });
    }
};
