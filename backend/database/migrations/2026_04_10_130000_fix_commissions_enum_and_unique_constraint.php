<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * MODIFIED BY: Commission Redesign v1.0
 *
 * CHANGELOG:
 * - Expand payee_role enum from ['super_agent', 'agent'] to include 'enumerator'
 * - Drop old unique constraint (lead_id, payee_role) — only 1 commission per role per lead
 *   which prevents two people of the same role from getting paid on the same lead
 * - Add new unique constraint (lead_id, payee_id) — one commission per PERSON per lead
 *   this allows multiple super_agents or agents to be paid if hierarchy has them
 */
return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Modify the payee_role enum to include 'enumerator'
        // MySQL requires DROP + RECREATE of enum column to change allowed values
        DB::statement("ALTER TABLE commissions MODIFY COLUMN payee_role ENUM('super_agent', 'agent', 'enumerator') NOT NULL");

        // Step 2: Add new unique constraint (lead_id, payee_id) FIRST
        // This ensures the foreign key on lead_id still has an index backing it
        // One commission per person per lead — allows >1 person of same role
        Schema::table('commissions', function (Blueprint $table) {
            $table->unique(['lead_id', 'payee_id'], 'unique_lead_payee_person');
        });

        // Step 3: Drop old unique constraint (lead_id, payee_role)
        // We check if it exists first to avoid errors on repeated runs
        $constraintExists = DB::select("
            SELECT COUNT(*) as cnt
            FROM information_schema.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'commissions'
              AND CONSTRAINT_NAME = 'unique_lead_payee_role'
        ");

        if ($constraintExists[0]->cnt > 0) {
            Schema::table('commissions', function (Blueprint $table) {
                $table->dropUnique('unique_lead_payee_role');
            });
        }

    }

    public function down(): void
    {
        // Reverse: drop new constraint
        Schema::table('commissions', function (Blueprint $table) {
            $table->dropUnique('unique_lead_payee_person');
        });

        // Restore old enum (removes enumerator)
        DB::statement("ALTER TABLE commissions MODIFY COLUMN payee_role ENUM('super_agent', 'agent') NOT NULL");

        // Restore old unique constraint
        Schema::table('commissions', function (Blueprint $table) {
            $table->unique(['lead_id', 'payee_role'], 'unique_lead_payee_role');
        });
    }
};
