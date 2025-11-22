<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('materials', 'physical_count')) {
            Schema::table('materials', function (Blueprint $table) {
                $table->decimal('physical_count', 12, 2)->nullable()->after('quantity');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('materials', 'physical_count')) {
            Schema::table('materials', function (Blueprint $table) {
                $table->dropColumn('physical_count');
            });
        }
    }
};