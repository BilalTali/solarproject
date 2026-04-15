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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
        });

        // Data Migration: Populate parent_id based on legacy fields
        
        // 1. Agents -> their Super Agent
        DB::statement("UPDATE users SET parent_id = super_agent_id WHERE role = 'agent' AND super_agent_id IS NOT NULL");

        // 2. Enumerators -> their Agent creator
        DB::statement("UPDATE users SET parent_id = created_by_agent_id WHERE role = 'enumerator' AND enumerator_creator_role = 'agent' AND created_by_agent_id IS NOT NULL AND parent_id IS NULL");

        // 3. Enumerators -> their Super Agent creator
        DB::statement("UPDATE users SET parent_id = created_by_super_agent_id WHERE role = 'enumerator' AND enumerator_creator_role = 'super_agent' AND created_by_super_agent_id IS NOT NULL AND parent_id IS NULL");

        // 4. Enumerators -> Admin (if creator role is admin, but no explicit admin field exists, find the first admin)
        $adminId = DB::table('users')->where('role', 'admin')->value('id');
        if ($adminId) {
            DB::statement("UPDATE users SET parent_id = ? WHERE role = 'enumerator' AND enumerator_creator_role = 'admin' AND parent_id IS NULL", [$adminId]);
            
            // 5. Super Agents -> Admin (they are directly under Admin)
            DB::statement("UPDATE users SET parent_id = ? WHERE role = 'super_agent' AND parent_id IS NULL", [$adminId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn('parent_id');
        });
    }
};
