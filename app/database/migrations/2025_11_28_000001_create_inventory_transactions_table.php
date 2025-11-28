<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('inventory_transactions')) {
            return; // already created by a later migration
        }
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('subject_type'); // 'material' or 'product'
            $table->unsignedBigInteger('subject_id');
            $table->string('type'); // 'in' or 'out'
            $table->decimal('quantity', 12, 2);
            $table->string('unit')->nullable();
            $table->string('name')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['subject_type', 'subject_id']);
            $table->index(['type']);
            $table->index(['created_by']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
    }
};