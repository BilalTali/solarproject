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
        Schema::table('commission_slabs', function (Blueprint $table) {
            $table->decimal('enumerator_commission', 10, 2)->default(0)->after('agent_commission');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('commission_slabs', function (Blueprint $table) {
            $table->dropColumn('enumerator_commission');
        });
    }
};
