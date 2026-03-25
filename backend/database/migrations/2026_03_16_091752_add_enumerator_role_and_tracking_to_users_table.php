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
        // 1. Alter the ENUM to include 'enumerator'
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'super_agent', 'agent', 'enumerator') NOT NULL DEFAULT 'agent'");

        // 2. Add tracking column if it doesn't already exist
        if (!Schema::hasColumn('users', 'created_by_agent_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('created_by_agent_id')->nullable()->after('super_agent_id');
                $table->index('created_by_agent_id', 'idx_enumerators_by_agent');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_enumerators_by_agent');
            $table->dropColumn('created_by_agent_id');
        });

        // Reverting enum safely requires checking existing data if any enumerators exist,
        // but for a strict rollback:
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'super_agent', 'agent') NOT NULL DEFAULT 'agent'");
    }
};
