<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->string('quotation_serial', 100)->nullable()->after('system_make');
            $table->string('receipt_serial', 100)->nullable()->after('quotation_serial');
            
            $table->decimal('quotation_base_amount', 12, 2)->nullable()->after('receipt_serial');
            $table->decimal('quotation_gst_amount', 12, 2)->nullable()->after('quotation_base_amount');
            $table->decimal('quotation_total_amount', 12, 2)->nullable()->after('quotation_gst_amount');
            
            $table->decimal('receipt_amount', 12, 2)->nullable()->after('quotation_total_amount');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn([
                'quotation_serial',
                'receipt_serial',
                'quotation_base_amount',
                'quotation_gst_amount',
                'quotation_total_amount',
                'receipt_amount'
            ]);
        });
    }
};
