<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->json('billing_items')->nullable()->after('receipt_amount');
            $table->decimal('billing_gst_percentage', 5, 2)->default(5.00)->after('billing_items');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn(['billing_items', 'billing_gst_percentage']);
        });
    }
};
