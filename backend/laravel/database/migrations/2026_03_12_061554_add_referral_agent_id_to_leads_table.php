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
            // Stores the agent_id code string entered by the beneficiary (e.g. "SM-2026-1042")
            // Nullable — only set when a referral code was provided on the public form
            $table->string('referral_agent_id', 20)->nullable()->after('source');

            // Index for admin reporting: "how many leads came via referral?"
            $table->index('referral_agent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropIndex(['referral_agent_id']);
            $table->dropColumn('referral_agent_id');
        });
    }
};
