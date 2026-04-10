<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * MODIFIED BY: Commission Redesign v1.0
 *
 * CHANGELOG:
 * - Fix commission_entry_status enum on leads table
 * - Old values: none, super_agent_entered, agent_entered, both_entered
 * - New values: none, partially_entered, all_entered
 *   (matches what CommissionService::refreshLeadCommissionStatus() already outputs)
 * - Data migration: maps old values → new values
 */
return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Temporarily convert to string so we can map values
        DB::statement("ALTER TABLE leads MODIFY COLUMN commission_entry_status VARCHAR(50) NOT NULL DEFAULT 'none'");

        // Step 2: Migrate existing data
        DB::statement("UPDATE leads SET commission_entry_status = 'none' WHERE commission_entry_status = 'none'");
        DB::statement("UPDATE leads SET commission_entry_status = 'partially_entered' WHERE commission_entry_status IN ('super_agent_entered', 'agent_entered')");
        DB::statement("UPDATE leads SET commission_entry_status = 'all_entered' WHERE commission_entry_status = 'both_entered'");

        // Step 3: Re-apply as proper enum with new values
        DB::statement("ALTER TABLE leads MODIFY COLUMN commission_entry_status ENUM('none', 'partially_entered', 'all_entered') NOT NULL DEFAULT 'none'");
    }

    public function down(): void
    {
        // Reverse to string first
        DB::statement("ALTER TABLE leads MODIFY COLUMN commission_entry_status VARCHAR(50) NOT NULL DEFAULT 'none'");

        // Map back (best effort — cannot perfectly restore super_agent_entered vs agent_entered)
        DB::statement("UPDATE leads SET commission_entry_status = 'both_entered' WHERE commission_entry_status = 'all_entered'");
        DB::statement("UPDATE leads SET commission_entry_status = 'super_agent_entered' WHERE commission_entry_status = 'partially_entered'");

        // Restore old enum
        DB::statement("ALTER TABLE leads MODIFY COLUMN commission_entry_status ENUM('none', 'super_agent_entered', 'agent_entered', 'both_entered') NOT NULL DEFAULT 'none'");
    }
};
