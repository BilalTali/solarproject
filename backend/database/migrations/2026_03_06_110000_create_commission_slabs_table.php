<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('commission_slabs', function (Blueprint $table) {
            $table->id();
            $table->enum('capacity', ['1kw', '2kw', '3kw', 'above_3kw'])->unique();
            $table->string('label');                         // "1 kW Solar System"
            $table->decimal('agent_commission', 10, 2);      // Agent payout
            $table->decimal('super_agent_override', 10, 2)->default(0); // SA override
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commission_slabs');
    }
};
