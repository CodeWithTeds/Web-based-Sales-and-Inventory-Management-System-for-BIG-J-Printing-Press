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
        // Drop from products if exists
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'client_ok')) {
                $table->dropColumn('client_ok');
            }
        });

        // Drop from materials if exists
        Schema::table('materials', function (Blueprint $table) {
            if (Schema::hasColumn('materials', 'client_ok')) {
                $table->dropColumn('client_ok');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add back to products if missing
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'client_ok')) {
                $table->boolean('client_ok')->default(false)->after('active');
            }
        });

        // Add back to materials if missing
        Schema::table('materials', function (Blueprint $table) {
            if (!Schema::hasColumn('materials', 'client_ok')) {
                $table->boolean('client_ok')->default(false)->after('unit_price');
            }
        });
    }
};
