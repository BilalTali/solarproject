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
        Schema::create('offer_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offer_id')->constrained('offers')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // -- RAW COUNTERS --
            $table->unsignedInteger('total_installations')->default(0);
            $table->unsignedInteger('redeemed_installations')->default(0);
            $table->unsignedInteger('redemption_count')->default(0);

            // -- DERIVED --
            $table->unsignedInteger('unredeemed_installations')->default(0);
            $table->boolean('can_redeem')->default(false);
            $table->unsignedInteger('pending_redemption_count')->default(0);

            // -- TIMESTAMPS --
            $table->timestamp('first_installation_at')->nullable();
            $table->timestamp('last_installation_at')->nullable();
            $table->timestamp('last_redeemed_at')->nullable();

            $table->timestamps();

            $table->unique(['offer_id', 'user_id'], 'uq_offer_user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offer_progress');
    }
};
