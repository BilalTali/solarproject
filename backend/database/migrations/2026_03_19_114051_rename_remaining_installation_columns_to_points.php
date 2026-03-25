<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            if (Schema::hasColumn('offers', 'current_installations') && !Schema::hasColumn('offers', 'current_points')) {
                $table->renameColumn('current_installations', 'current_points');
            }
        });

        Schema::table('offer_progress', function (Blueprint $table) {
            if (Schema::hasColumn('offer_progress', 'total_installations') && !Schema::hasColumn('offer_progress', 'total_points')) {
                $table->renameColumn('total_installations', 'total_points');
            }
            if (Schema::hasColumn('offer_progress', 'redeemed_installations') && !Schema::hasColumn('offer_progress', 'redeemed_points')) {
                $table->renameColumn('redeemed_installations', 'redeemed_points');
            }
            if (Schema::hasColumn('offer_progress', 'unredeemed_installations') && !Schema::hasColumn('offer_progress', 'unredeemed_points')) {
                $table->renameColumn('unredeemed_installations', 'unredeemed_points');
            }
        });
    }

    public function down(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->renameColumn('current_points', 'current_installations');
        });

        Schema::table('offer_progress', function (Blueprint $table) {
            $table->renameColumn('total_points', 'total_installations');
            $table->renameColumn('redeemed_points', 'redeemed_installations');
            $table->renameColumn('unredeemed_points', 'unredeemed_installations');
        });
    }
};
