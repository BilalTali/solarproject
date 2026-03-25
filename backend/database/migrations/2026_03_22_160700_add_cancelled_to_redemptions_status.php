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
        // Use raw SQL to handle ENUM modification
        DB::statement("ALTER TABLE offer_redemptions MODIFY COLUMN status ENUM('pending', 'approved', 'delivered', 'rejected', 'cancelled') NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE offer_redemptions MODIFY COLUMN status ENUM('pending', 'approved', 'delivered', 'rejected') NOT NULL DEFAULT 'pending'");
    }
};
