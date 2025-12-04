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
        if (Schema::hasTable('product_size') && !Schema::hasColumn('product_size', 'quantity')) {
            Schema::table('product_size', function (Blueprint $table) {
                $table->unsignedInteger('quantity')->default(0)->after('size_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('product_size') && Schema::hasColumn('product_size', 'quantity')) {
            Schema::table('product_size', function (Blueprint $table) {
                $table->dropColumn('quantity');
            });
        }
    }
};

