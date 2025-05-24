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
        Schema::create('reviews', function (Blueprint $table) {
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade')->onUpdate('cascade'); // FK đến customers(id) [cite: 71, 107]
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade')->onUpdate('cascade'); // FK đến products(id) [cite: 71, 107]
            $table->integer('rating')->nullable(); // Điểm đánh giá (ví dụ: 1–5 sao) [cite: 71, 107]
            $table->text('comment')->nullable(); // Nội dung nhận xét [cite: 71, 107]
            $table->timestamps(); // created_at DATETIME, updated_at DATETIME [cite: 71, 107]
            
            // Một khách hàng chỉ review 1 lần 1 sản phẩm [cite: 71, 107]
            $table->primary(['customer_id', 'product_id']); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};