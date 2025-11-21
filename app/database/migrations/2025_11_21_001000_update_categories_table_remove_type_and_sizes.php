<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Drop composite unique and index involving type, if present
        try {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropUnique(['name', 'type']);
            });
        } catch (\Throwable $e) {}

        try {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropIndex(['type', 'status']);
            });
        } catch (\Throwable $e) {}

        // Drop columns if they exist
        if (Schema::hasColumn('categories', 'type')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropColumn('type');
            });
        }
        if (Schema::hasColumn('categories', 'sizes')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropColumn('sizes');
            });
        }

        // Ensure unique on name exists
        try {
            Schema::table('categories', function (Blueprint $table) {
                $table->unique('name');
            });
        } catch (\Throwable $e) {}
    }

    public function down(): void
    {
        // Recreate columns (best-effort) and indexes
        if (!Schema::hasColumn('categories', 'type')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->string('type')->nullable();
            });
        }
        // sizes intentionally not restored

        // Drop unique(name) if present, then restore original unique/index with type
        try {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropUnique(['name']);
            });
        } catch (\Throwable $e) {}

        try {
            Schema::table('categories', function (Blueprint $table) {
                $table->unique(['name', 'type']);
                $table->index(['type', 'status']);
            });
        } catch (\Throwable $e) {}
    }
};