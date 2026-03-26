<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Alter the ENUM safely using raw SQL
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'super_agent', 'agent', 'enumerator', 'operator') NOT NULL DEFAULT 'agent'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverting ENUM requires ensuring no users have the operator role
        DB::statement("UPDATE users SET role = 'agent' WHERE role = 'operator'");
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'super_agent', 'agent', 'enumerator') NOT NULL DEFAULT 'agent'");
    }
};
