<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('incentive_offers', function (Blueprint $table) {
            $table->id();
            $table->string('title');                          // "Umrah Trip"
            $table->text('description')->nullable();
            $table->enum('offer_type', ['physical', 'cash', 'trip'])->default('cash');
            $table->unsignedInteger('target_installs');       // Installs needed to qualify
            $table->decimal('reward_value', 10, 2)->nullable(); // Cash equivalent / value
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('image_url')->nullable();          // Optional offer image
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incentive_offers');
    }
};
