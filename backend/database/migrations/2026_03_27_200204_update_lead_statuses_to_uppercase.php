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
        // Convert all existing lead statuses to UPPERCASE to conform with the new pipeline architecture
        DB::statement('UPDATE leads SET status = UPPER(status) WHERE status IS NOT NULL');
        
        // Convert the audit logs as well to maintain referential integrity
        DB::statement('UPDATE lead_status_logs SET from_status = UPPER(from_status) WHERE from_status IS NOT NULL');
        DB::statement('UPDATE lead_status_logs SET to_status = UPPER(to_status) WHERE to_status IS NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to lowercase for legacy support
        DB::statement('UPDATE leads SET status = LOWER(status) WHERE status IS NOT NULL');
        
        DB::statement('UPDATE lead_status_logs SET from_status = LOWER(from_status) WHERE from_status IS NOT NULL');
        DB::statement('UPDATE lead_status_logs SET to_status = LOWER(to_status) WHERE to_status IS NOT NULL');
    }
};
