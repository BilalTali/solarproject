<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Extend verification_status ENUM to include enumerator-agent verification pipeline values.
     * Also extend source ENUM to include 'enumerator_submission'.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return; // SQLite doesn't support MODIFY COLUMN
        }

        DB::statement("ALTER TABLE leads MODIFY COLUMN verification_status ENUM(
            'not_required',
            'pending_super_agent_verification',
            'super_agent_verified',
            'reverted_to_agent',
            'reverted_to_enumerator',
            'pending_agent_verification',
            'admin_override'
        ) NOT NULL DEFAULT 'not_required'");

        DB::statement("ALTER TABLE leads MODIFY COLUMN source ENUM(
            'public_form',
            'agent_submission',
            'super_agent_submission',
            'admin_manual',
            'enumerator_submission'
        ) NOT NULL DEFAULT 'public_form'");
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        // Revert to original values (strip enumerator-related entries)
        DB::statement("ALTER TABLE leads MODIFY COLUMN verification_status ENUM(
            'not_required',
            'pending_super_agent_verification',
            'super_agent_verified',
            'reverted_to_agent',
            'admin_override'
        ) NOT NULL DEFAULT 'not_required'");

        DB::statement("ALTER TABLE leads MODIFY COLUMN source ENUM(
            'public_form',
            'agent_submission',
            'super_agent_submission',
            'admin_manual'
        ) NOT NULL DEFAULT 'public_form'");
    }
};
