<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add column to leads if it doesn't exist
        if (!Schema::hasColumn('leads', 'submitted_by_enumerator_id')) {
            Schema::table('leads', function (Blueprint $table) {
                $table->unsignedBigInteger('submitted_by_enumerator_id')->nullable()->after('submitted_by_agent_id');
                $table->index('submitted_by_enumerator_id', 'idx_leads_by_enumerator');
            });
        }

        // 2. Update Leads source ENUM
        DB::statement("ALTER TABLE leads MODIFY COLUMN source ENUM('public_form', 'agent_submission', 'super_agent_submission', 'enumerator_submission') NOT NULL");

        // 3. Update Commissions payee_role ENUM
        DB::statement("ALTER TABLE commissions MODIFY COLUMN payee_role ENUM('agent', 'super_agent', 'admin', 'enumerator') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropIndex('idx_leads_by_enumerator');
            $table->dropColumn('submitted_by_enumerator_id');
        });

        DB::statement("ALTER TABLE leads MODIFY COLUMN source ENUM('public_form', 'agent_submission', 'super_agent_submission') NOT NULL");
        DB::statement("ALTER TABLE commissions MODIFY COLUMN payee_role ENUM('agent', 'super_agent', 'admin') NOT NULL");
    }
};
