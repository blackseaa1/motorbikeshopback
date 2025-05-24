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
        Schema::create('product_images', function (Blueprint $table) {
            $table->id(); // INT PRIMARY KEY AUTO_INCREMENT [cite: 63, 102]
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade')->onUpdate('cascade'); // FK đến products(id) [cite: 63, 102]
            $table->text('image_url')->nullable(); // TEXT, Link ảnh sản phẩm [cite: 63, 102]
            $table->timestamps(); // created_at DATETIME, updated_at DATETIME [cite: 63, 102]
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_images');
    }
};