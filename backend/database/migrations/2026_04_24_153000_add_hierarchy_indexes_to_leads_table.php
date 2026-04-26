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
        Schema::table('leads', function (Blueprint $table) {
            $table->index('assigned_super_agent_id');
            $table->index('submitted_by_agent_id');
            $table->index('submitted_by_enumerator_id');
            $table->index('owner_type');
            $table->index('verification_status');
            $table->index('source');
            $table->index('assigned_admin_id');
            $table->index('wa_handler_admin_id');
            $table->index('created_by_super_agent_id');
            $table->index('assigned_agent_id');
            $table->index('beneficiary_state');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropIndex(['assigned_super_agent_id']);
            $table->dropIndex(['submitted_by_agent_id']);
            $table->dropIndex(['submitted_by_enumerator_id']);
            $table->dropIndex(['owner_type']);
            $table->dropIndex(['verification_status']);
            $table->dropIndex(['source']);
            $table->dropIndex(['assigned_admin_id']);
            $table->dropIndex(['wa_handler_admin_id']);
            $table->dropIndex(['created_by_super_agent_id']);
            $table->dropIndex(['assigned_agent_id']);
            $table->dropIndex(['beneficiary_state']);
        });
    }
};
