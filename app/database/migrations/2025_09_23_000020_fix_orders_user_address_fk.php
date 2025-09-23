<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Drop the existing incorrect foreign key if it exists
            // Conventionally named: orders_user_address_id_foreign
            $table->dropForeign(['user_address_id']);
        });

        Schema::table('orders', function (Blueprint $table) {
            // Re-create the foreign key correctly to users_addresses table
            $table->foreign('user_address_id')
                ->references('id')
                ->on('users_addresses')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Drop the corrected foreign key
            $table->dropForeign(['user_address_id']);
        });

        Schema::table('orders', function (Blueprint $table) {
            // Restore the previous (incorrect) foreign key referencing user_addresses
            $table->foreign('user_address_id')
                ->references('id')
                ->on('user_addresses')
                ->nullOnDelete();
        });
    }
};