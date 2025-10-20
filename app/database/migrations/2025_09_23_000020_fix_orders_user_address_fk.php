<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Drop the existing foreign key if it exists
            try {
                $table->dropForeign(['user_address_id']);
            } catch (\Throwable $e) {
                // ignore if not existing
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            // Re-create the foreign key correctly to users_addresses table
            if (Schema::hasColumn('orders', 'user_address_id') && Schema::hasTable('users_addresses')) {
                $table->foreign('user_address_id')
                    ->references('id')
                    ->on('users_addresses')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Drop the corrected foreign key
            try {
                $table->dropForeign(['user_address_id']);
            } catch (\Throwable $e) {}
        });

        Schema::table('orders', function (Blueprint $table) {
            // Restore the previous (incorrect) foreign key referencing user_addresses
            if (Schema::hasColumn('orders', 'user_address_id') && Schema::hasTable('user_addresses')) {
                $table->foreign('user_address_id')
                    ->references('id')
                    ->on('user_addresses')
                    ->nullOnDelete();
            }
        });
    }
};