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
        Schema::create('crm_options', function (Blueprint $table) {
            $table->id();
            $table->string('category')->index(); // e.g. system_capacity, discom, roof_size
            $table->string('label');             // e.g. "3 kW", "JPDCL"
            $table->string('value');             // e.g. "3kw", "JPDCL"
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // We also need to relax 'roof_size' to a string in 'leads' table to prevent enum blocking.
        // (system_capacity was already relaxed in a previous migration)
        Schema::table('leads', function (Blueprint $table) {
            $table->string('roof_size')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverting string back to enum is unsafe without data loss guarantees, but we drop the new table.
        Schema::dropIfExists('crm_options');
    }
};
