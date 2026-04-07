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
        Schema::table('media', function (Blueprint $table) {
            $table->string('winner_name')->nullable()->after('title');
        });

        Schema::table('achievements', function (Blueprint $table) {
            $table->string('winner_name')->nullable()->after('title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropColumn('winner_name');
        });

        Schema::table('achievements', function (Blueprint $table) {
            $table->dropColumn('winner_name');
        });
    }
};
