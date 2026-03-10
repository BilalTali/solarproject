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
        Schema::table('offers', function (Blueprint $table) {
            $table->tinyInteger('grace_period_days')
                  ->unsigned()
                  ->default(7)
                  ->after('offer_to')
                  ->comment('Days after offer_to that eligible agents can still claim');

            $table->boolean('is_annual')
                  ->default(false)
                  ->after('is_featured')
                  ->comment('Visual flag for annual offers (300+ day offers auto-set this)');

            $table->timestamp('absorption_processed_at')
                  ->nullable()
                  ->after('is_annual')
                  ->comment('When the expiry absorption job ran for this offer — null = not yet processed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->dropColumn(['grace_period_days', 'is_annual', 'absorption_processed_at']);
        });
    }
};
