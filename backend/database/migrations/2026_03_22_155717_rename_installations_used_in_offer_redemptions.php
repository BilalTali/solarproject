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
        Schema::table('offer_redemptions', function (Blueprint $table) {
            $table->renameColumn('installations_used', 'points_used');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('offer_redemptions', function (Blueprint $table) {
            $table->renameColumn('points_used', 'installations_used');
        });
    }
};
