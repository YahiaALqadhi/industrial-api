<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->foreignId('category_id')->constrained()->cascadeOnDelete();

            $table->string('name');
            $table->string('slug')->unique();

            $table->string('sku')->nullable()->unique();

            $table->decimal('price', 12, 2)->default(0);
            $table->decimal('discount_price', 12, 2)->nullable();

            $table->integer('stock')->default(0);

            $table->string('brand')->nullable();

            $table->string('main_image')->nullable();

            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();

            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};