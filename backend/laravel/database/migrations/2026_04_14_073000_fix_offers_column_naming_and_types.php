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
            // 1. Rename column if it exists as installations
            if (Schema::hasColumn('offers', 'target_installations') && !Schema::hasColumn('offers', 'target_points')) {
                $table->renameColumn('target_installations', 'target_points');
            }

            // 2. Ensure types are decimal for point consistency
            // Note: In Laravel 10+, change() is native if supported by the DB driver
            $table->decimal('target_points', 10, 2)->change();
            $table->decimal('current_points', 10, 2)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->integer('target_points')->unsigned()->change();
            $table->integer('current_points')->unsigned()->change();
            $table->renameColumn('target_points', 'target_installations');
        });
    }
};
