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
        Schema::table('users_addresses', function (Blueprint $table) {
            if (!Schema::hasColumn('users_addresses', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('exact_address');
            }
            if (!Schema::hasColumn('users_addresses', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users_addresses', function (Blueprint $table) {
            if (Schema::hasColumn('users_addresses', 'longitude')) {
                $table->dropColumn('longitude');
            }
            if (Schema::hasColumn('users_addresses', 'latitude')) {
                $table->dropColumn('latitude');
            }
        });
    }
};