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
        Schema::create('offer_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offer_id')->constrained('offers')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            $table->unsignedTinyInteger('redemption_number')->default(1);
            $table->unsignedInteger('installations_used');

            // -- STATUS --
            $table->enum('status', ['pending', 'approved', 'delivered'])->default('pending');

            // -- ADMIN HANDLING --
            $table->text('notes')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('delivered_at')->nullable();

            // -- AGENT CLAIM --
            $table->timestamp('claimed_at')->useCurrent();

            $table->timestamps();

            $table->index(['offer_id', 'user_id'], 'idx_offer_user');
            $table->index('status', 'idx_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offer_redemptions');
    }
};
