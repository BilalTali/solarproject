<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE commissions MODIFY COLUMN payee_role ENUM('super_agent', 'agent', 'enumerator', 'field_technical_team') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE commissions MODIFY COLUMN payee_role ENUM('super_agent', 'agent', 'enumerator') NOT NULL");
    }
};
