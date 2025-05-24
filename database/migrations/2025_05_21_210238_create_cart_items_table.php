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
        Schema::create('cart_items', function (Blueprint $table) {
            $table->foreignId('cart_id')->constrained('carts')->onDelete('cascade')->onUpdate('cascade'); // FK đến carts(id) [cite: 75, 109]
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade')->onUpdate('cascade'); // FK đến products(id) [cite: 75, 109]
            $table->integer('quantity'); // Số lượng sản phẩm trong giỏ hàng [cite: 75, 109]
            $table->timestamps(); // created_at DATETIME, updated_at DATETIME [cite: 75, 109]
            
            // Một sản phẩm chỉ được thêm 1 lần vào mỗi giỏ hàng [cite: 75, 109]
            $table->primary(['cart_id', 'product_id']); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};