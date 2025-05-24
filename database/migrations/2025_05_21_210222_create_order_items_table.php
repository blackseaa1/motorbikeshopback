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
        Schema::create('order_items', function (Blueprint $table) {
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade')->onUpdate('cascade'); // FK đến orders(id) [cite: 69, 106]
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade')->onUpdate('cascade'); // FK đến products(id) [cite: 69, 106] (SQL gốc là CASCADE, nên nếu sản phẩm bị xóa, item này cũng bị xóa khỏi đơn hàng)
            $table->integer('quantity'); // Số lượng sản phẩm trong đơn hàng [cite: 69, 106]
            $table->decimal('price', 10, 2); // Giá của sản phẩm tại thời điểm đặt [cite: 69, 106]
            $table->timestamps(); // created_at DATETIME, updated_at DATETIME [cite: 69, 106]
            
            // Không cho phép cùng sản phẩm trong một đơn lặp lại [cite: 69, 106]
            $table->primary(['order_id', 'product_id']); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};