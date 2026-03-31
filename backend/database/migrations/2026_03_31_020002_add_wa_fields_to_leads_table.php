<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            // Which designated handler admin this WA chatbot lead is assigned to.
            // NULL = no handler designated (falls to general admin pool).
            // Only set when source = 'whatsapp_chatbot' AND no referral code was provided.
            $table->unsignedBigInteger('wa_handler_admin_id')
                ->nullable()
                ->after('source');

            $table->foreign('wa_handler_admin_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });

        // Extend source ENUM to include whatsapp_chatbot.
        // Skipped for SQLite (used in tests).
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE leads MODIFY COLUMN source ENUM(
                'public_form',
                'agent_submission',
                'super_agent_submission',
                'admin_manual',
                'enumerator_submission',
                'whatsapp_chatbot'
            ) NOT NULL DEFAULT 'public_form'");
        }
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(['wa_handler_admin_id']);
            $table->dropColumn('wa_handler_admin_id');
        });

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE leads MODIFY COLUMN source ENUM(
                'public_form',
                'agent_submission',
                'super_agent_submission',
                'admin_manual',
                'enumerator_submission'
            ) NOT NULL DEFAULT 'public_form'");
        }
    }
};
