<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Delete any existing CallHippo integrations before changing the enum
        DB::table('phone_integrations')->where('type', 'callhippo')->delete();

        DB::statement("ALTER TABLE phone_integrations MODIFY COLUMN type ENUM('freepbx','vicidial','custom_sip') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE phone_integrations MODIFY COLUMN type ENUM('callhippo','freepbx','vicidial','custom_sip') NOT NULL");
    }
};
