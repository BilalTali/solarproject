<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $hasUsersMobileUnique = $this->hasIndex('users', 'users_mobile_unique');
        $hasUsersEmailUnique = $this->hasIndex('users', 'users_email_unique');
        $hasLeadsConsumerNumberUnique = $this->hasIndex('leads', 'leads_consumer_number_unique');

        Schema::table('users', function (Blueprint $table) use ($hasUsersMobileUnique, $hasUsersEmailUnique) {
            if (!$hasUsersMobileUnique) $table->unique('mobile', 'users_mobile_unique');
            if (!$hasUsersEmailUnique) $table->unique('email', 'users_email_unique');
        });

        Schema::table('leads', function (Blueprint $table) use ($hasLeadsConsumerNumberUnique) {
            if (!$hasLeadsConsumerNumberUnique) $table->unique('consumer_number', 'leads_consumer_number_unique');
        });
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        $indexes = DB::select("SHOW INDEXES FROM {$table} WHERE Key_name = '{$indexName}'");
        return count($indexes) > 0;
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropUnique('leads_consumer_number_unique');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_email_unique');
            $table->dropUnique('users_mobile_unique');
        });
    }
};
