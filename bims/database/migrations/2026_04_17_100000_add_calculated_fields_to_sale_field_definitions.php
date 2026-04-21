<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Extend the enum to include 'calculated'
        DB::statement("ALTER TABLE sale_field_definitions
            MODIFY COLUMN field_type
            ENUM('text','number','date','select','textarea','checkbox','calculated')
            NOT NULL DEFAULT 'text'");

        Schema::table('sale_field_definitions', function (Blueprint $table) {
            $table->text('formula')->nullable()->after('options')
                  ->comment('ExpressionLanguage formula for calculated fields');
        });
    }

    public function down(): void
    {
        Schema::table('sale_field_definitions', function (Blueprint $table) {
            $table->dropColumn('formula');
        });

        DB::statement("ALTER TABLE sale_field_definitions
            MODIFY COLUMN field_type
            ENUM('text','number','date','select','textarea','checkbox')
            NOT NULL DEFAULT 'text'");
    }
};
