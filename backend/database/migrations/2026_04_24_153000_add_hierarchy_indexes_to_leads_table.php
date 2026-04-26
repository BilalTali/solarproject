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
        $indexes = [
            'assigned_super_agent_id',
            'submitted_by_agent_id',
            'submitted_by_enumerator_id',
            'owner_type',
            'verification_status',
            'source',
            'assigned_admin_id',
            'wa_handler_admin_id',
            'created_by_super_agent_id',
            'assigned_agent_id',
            'beneficiary_state'
        ];

        foreach ($indexes as $column) {
            $indexName = 'leads_' . $column . '_index';
            $exists = count(\Illuminate\Support\Facades\DB::select("SHOW INDEX FROM `leads` WHERE Key_name = ?", [$indexName])) > 0;
            
            if (!$exists) {
                Schema::table('leads', function (Blueprint $table) use ($column) {
                    $table->index($column);
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $indexes = [
            'assigned_super_agent_id',
            'submitted_by_agent_id',
            'submitted_by_enumerator_id',
            'owner_type',
            'verification_status',
            'source',
            'assigned_admin_id',
            'wa_handler_admin_id',
            'created_by_super_agent_id',
            'assigned_agent_id',
            'beneficiary_state'
        ];

        foreach ($indexes as $column) {
            $indexName = 'leads_' . $column . '_index';
            $exists = count(\Illuminate\Support\Facades\DB::select("SHOW INDEX FROM `leads` WHERE Key_name = ?", [$indexName])) > 0;
            
            if ($exists) {
                Schema::table('leads', function (Blueprint $table) use ($column) {
                    $table->dropIndex([$column]);
                });
            }
        }
    }
};
