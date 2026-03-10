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
        Schema::table('offer_progress', function (Blueprint $table) {
            $table->enum('role_context', ['agent', 'super_agent'])
                  ->default('agent')
                  ->after('user_id')
                  ->comment('Role in which this user is participating — super agents participating
                             as agents have role_context=super_agent to distinguish from agent rows');

            $table->timestamp('offer_ended_zeroed_at')
                  ->nullable()
                  ->after('last_redeemed_at')
                  ->comment('When this progress was zeroed on offer expiry for UI display');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('offer_progress', function (Blueprint $table) {
            $table->dropColumn(['role_context', 'offer_ended_zeroed_at']);
        });
    }
};
