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
            // Re-adding defaults that were lost during the previous migration's change()
            $table->decimal('target_points', 10, 2)->default(0)->change();
            $table->decimal('current_points', 10, 2)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->decimal('target_points', 10, 2)->change();
            $table->decimal('current_points', 10, 2)->change();
        });
    }
};
