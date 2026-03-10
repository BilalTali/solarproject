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
        Schema::create('offers', function (Blueprint $table) {
            $table->id();

            // -- CORE --
            $table->string('title');
            $table->text('description')->nullable();

            // -- PRIZE --
            $table->string('prize_label');
            $table->decimal('prize_amount', 10, 2)->nullable();
            $table->string('prize_image_path', 500)->nullable();

            // -- TIMELINE --
            $table->date('offer_from');
            $table->date('offer_to');

            // -- TARGET --
            $table->unsignedInteger('target_installations');

            // -- TYPE --
            $table->enum('offer_type', ['individual', 'collective'])->default('individual');

            // -- VISIBILITY --
            $table->enum('visible_to', ['agents', 'super_agents', 'both'])->default('agents');

            // -- STATUS --
            $table->enum('status', ['active', 'paused', 'ended'])->default('active');

            // -- COLLECTIVE TRACKING --
            $table->unsignedInteger('current_installations')->default(0);
            $table->boolean('collective_redeemed')->default(false);
            $table->timestamp('collective_redeemed_at')->nullable();

            // -- DISPLAY --
            $table->unsignedTinyInteger('display_order')->default(0);
            $table->boolean('is_featured')->default(false);

            // -- META --
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'offer_from', 'offer_to'], 'idx_status_dates');
            $table->index('visible_to', 'idx_visible_to');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};
