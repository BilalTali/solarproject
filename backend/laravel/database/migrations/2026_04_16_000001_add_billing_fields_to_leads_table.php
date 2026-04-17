<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds billing/invoice fields to the leads table so each lead
     * can have a unique bill serial number and specific item/make selections.
     */
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            // Unique invoice/bill serial number (e.g. INV-2026-001)
            $table->string('bill_serial', 50)->nullable()->unique()->after('govt_application_number');

            // Date the bill was formally issued (defaults to created_at if not set)
            $table->date('bill_date')->nullable()->after('bill_serial');

            // The specific hardware item selected from the billing_items_json catalog
            $table->string('system_item', 255)->nullable()->after('bill_date');

            // The brand/manufacturer selected from the billing_makes_json catalog
            $table->string('system_make', 100)->nullable()->after('system_item');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn(['bill_serial', 'bill_date', 'system_item', 'system_make']);
        });
    }
};
