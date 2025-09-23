<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            // Link to orders (do not enforce uniqueness; allow multiple payment attempts per order)
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();

            // Optional metadata about the payment provider/method
            $table->string('provider')->nullable(); // e.g., paymongo
            $table->string('method')->nullable();   // e.g., gcash, card

            // Amount and currency
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('currency', 3)->default('PHP');


            // External reference/identifier (provider reference or our own)
            $table->string('reference')->nullable()->index();

            // Optional timestamps for lifecycle
            $table->timestamp('paid_at')->nullable();

            // Raw provider payloads and error messages (for troubleshooting)
            $table->json('payload')->nullable();
            $table->string('error_message')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};