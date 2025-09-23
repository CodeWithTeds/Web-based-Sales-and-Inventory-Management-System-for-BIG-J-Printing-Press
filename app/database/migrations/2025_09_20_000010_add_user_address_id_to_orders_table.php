<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Only add the column if it does not exist
            if (!Schema::hasColumn('orders', 'user_address_id')) {
                $table->foreignId('user_address_id')
                    ->nullable()
                    ->after('user_id');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            // Ensure the correct foreign key is set up
            // First drop any existing FK to avoid duplicate FK creation
            try {
                $table->dropForeign(['user_address_id']);
            } catch (\Throwable $e) {
                // ignore if not existing
            }

            $table->foreign('user_address_id')
                ->references('id')
                ->on('users_addresses')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Drop FK if exists
            try {
                $table->dropForeign(['user_address_id']);
            } catch (\Throwable $e) {}

            // Drop column if exists
            if (Schema::hasColumn('orders', 'user_address_id')) {
                $table->dropColumn('user_address_id');
            }
        });
    }
};