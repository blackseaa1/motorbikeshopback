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
        Schema::create('products', function (Blueprint $table) {
            $table->id(); // INT PRIMARY KEY AUTO_INCREMENT [cite: 61, 100]
            $table->string('name'); // VARCHAR(255) NOT NULL, Tên sản phẩm [cite: 61, 100]
            $table->text('description')->nullable(); // TEXT, Mô tả sản phẩm [cite: 61, 100]
            $table->foreignId('category_id')->constrained('categories')->onDelete('restrict')->onUpdate('cascade'); // FK đến categories(id) [cite: 61, 101]
            $table->foreignId('brand_id')->constrained('brands')->onDelete('restrict')->onUpdate('cascade'); // FK đến brands(id) [cite: 61, 101]
            $table->decimal('price', 10, 2); // DECIMAL(10,2) NOT NULL, Giá sản phẩm [cite: 61, 100]
            $table->integer('stock_quantity')->default(0); // INT NOT NULL DEFAULT 0, Số lượng tồn kho [cite: 61, 100]
            $table->string('material', 100)->nullable(); // VARCHAR(100), Chất liệu [cite: 61, 100]
            $table->string('color', 100)->nullable(); // VARCHAR(100), Màu sắc [cite: 61, 100]
            $table->text('specifications')->nullable(); // TEXT, Thông số kỹ thuật [cite: 61, 100]
            $table->boolean('is_active')->default(true); // BOOLEAN NOT NULL DEFAULT TRUE, Trạng thái hiển thị [cite: 61, 100]
            $table->timestamps(); // created_at DATETIME, updated_at DATETIME [cite: 61, 100]
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};