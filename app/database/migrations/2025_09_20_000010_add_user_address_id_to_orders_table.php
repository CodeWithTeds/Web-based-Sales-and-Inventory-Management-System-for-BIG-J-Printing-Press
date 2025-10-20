<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

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

        // Ensure the correct foreign key is set up
        // First drop any existing FK to avoid duplicate FK creation (if present)
        $constraint = DB::selectOne("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'user_address_id' AND REFERENCED_TABLE_NAME IS NOT NULL");
        if ($constraint && isset($constraint->CONSTRAINT_NAME)) {
            DB::statement("ALTER TABLE `orders` DROP FOREIGN KEY `{$constraint->CONSTRAINT_NAME}`");
        }

        Schema::table('orders', function (Blueprint $table) {
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
        // Drop FK if exists using information_schema
        $constraint = DB::selectOne("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'user_address_id' AND REFERENCED_TABLE_NAME IS NOT NULL");
        if ($constraint && isset($constraint->CONSTRAINT_NAME)) {
            DB::statement("ALTER TABLE `orders` DROP FOREIGN KEY `{$constraint->CONSTRAINT_NAME}`");
        }

        Schema::table('orders', function (Blueprint $table) {
            // Drop column if exists
            if (Schema::hasColumn('orders', 'user_address_id')) {
                $table->dropColumn('user_address_id');
            }
        });
    }
};