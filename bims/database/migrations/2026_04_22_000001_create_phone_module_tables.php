<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('phone_integrations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['callhippo', 'freepbx', 'vicidial', 'custom_sip']);
            $table->boolean('is_active')->default(false);

            // API credentials (cloud providers)
            $table->string('api_key')->nullable();
            $table->string('api_secret')->nullable();
            $table->string('webhook_secret')->nullable();
            $table->string('account_id')->nullable();

            // SIP / WebSocket (self-hosted + custom)
            $table->string('sip_domain')->nullable();
            $table->unsignedSmallInteger('sip_port')->default(5060);
            $table->enum('sip_transport', ['wss', 'ws', 'tcp', 'udp'])->default('wss');
            $table->string('websocket_url')->nullable();
            $table->string('stun_server')->default('stun:stun.l.google.com:19302');
            $table->string('turn_server')->nullable();
            $table->string('turn_username')->nullable();
            $table->string('turn_password')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('call_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('phone_integration_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('sale_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('direction', ['inbound', 'outbound']);
            $table->string('caller_number', 30)->nullable();
            $table->string('callee_number', 30)->nullable();
            $table->enum('status', ['ringing', 'connected', 'completed', 'missed', 'failed'])->default('ringing');
            $table->enum('disposition', ['answered', 'no_answer', 'busy', 'voicemail', 'failed'])->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('answered_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->string('recording_url')->nullable();
            $table->string('external_call_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        // SIP credentials on employees
        Schema::table('employees', function (Blueprint $table) {
            $table->string('sip_extension', 20)->nullable()->after('metadata');
            $table->string('sip_password')->nullable()->after('sip_extension');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['sip_extension', 'sip_password']);
        });
        Schema::dropIfExists('call_logs');
        Schema::dropIfExists('phone_integrations');
    }
};
