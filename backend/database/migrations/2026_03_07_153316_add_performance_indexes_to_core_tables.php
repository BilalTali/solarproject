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
            $table->index('status');
            $table->index('commission_entry_status');
            $table->index('beneficiary_mobile');
            $table->index('consumer_number');
            $table->index('created_at'); // Often used for sorting
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index('role');
            $table->index('status');
        });

        Schema::table('commissions', function (Blueprint $table) {
            $table->index('payment_status');
            $table->index(['payee_id', 'payee_role']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['commission_entry_status']);
            $table->dropIndex(['beneficiary_mobile']);
            $table->dropIndex(['consumer_number']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropIndex(['status']);
        });

        Schema::table('commissions', function (Blueprint $table) {
            $table->dropIndex(['payment_status']);
            $table->dropIndex(['payee_id', 'payee_role']);
        });
    }
};
