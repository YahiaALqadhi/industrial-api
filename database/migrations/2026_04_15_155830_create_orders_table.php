<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('order_number')->unique();

            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('shipping_cost', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);

            $table->string('currency')->default('USD');

            $table->enum('status', [
                'pending',
                'confirmed',
                'processing',
                'shipped',
                'delivered',
                'cancelled',
            ])->default('pending');

            $table->enum('payment_status', [
                'pending',
                'paid',
                'failed',
                'refunded',
            ])->default('pending');

            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();

            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->text('shipping_address')->nullable();

            $table->text('notes')->nullable();

            $table->timestamp('ordered_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};