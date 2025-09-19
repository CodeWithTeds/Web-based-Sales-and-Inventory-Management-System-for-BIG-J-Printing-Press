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
        Schema::dropIfExists('product_materials');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('product_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('material_id')->constrained()->onDelete('cascade');
            $table->decimal('quantity_required', 10, 3)->comment('Amount of material needed per product unit');
            $table->string('unit')->comment('Unit of measurement for the material');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Add a unique constraint to prevent duplicate entries
            $table->unique(['product_id', 'material_id']);
        });
    }
};
