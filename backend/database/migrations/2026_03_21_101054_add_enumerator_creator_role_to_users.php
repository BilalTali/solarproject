<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Tracks which role created this enumerator — drives lead routing and commission chain
            $table->enum('enumerator_creator_role', ['admin', 'super_agent', 'agent'])
                ->nullable()
                ->after('created_by_agent_id')
                ->comment('For enumerator role only: who created this enumerator');

            // FK for SA-created enumerators (column exists for agents but also needs to serve enumerators)
            // created_by_super_agent_id already exists from prior migration — no need to re-add
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('enumerator_creator_role');
        });
    }
};
