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
        Schema::table('offer_redemptions', function (Blueprint $table) {
            $table->unsignedBigInteger('admin_approved_by')->nullable()->after('status');
            $table->timestamp('admin_approved_at')->nullable()->after('claimed_at');
            
            $table->foreign('admin_approved_by')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();
        });

        // Modifying the enum safely since MySQL requires DB::statement
        DB::statement("ALTER TABLE offer_redemptions MODIFY COLUMN status ENUM('pending', 'admin_approved', 'approved', 'delivered', 'rejected', 'cancelled') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('offer_redemptions', function (Blueprint $table) {
            $table->dropForeign(['admin_approved_by']);
            $table->dropColumn('admin_approved_by');
            $table->dropColumn('admin_approved_at');
        });

        // Revert enum safely
        DB::statement("ALTER TABLE offer_redemptions MODIFY COLUMN status ENUM('pending', 'approved', 'delivered', 'rejected', 'cancelled') DEFAULT 'pending'");
    }
};
