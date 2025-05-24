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
        Schema::create('categories', function (Blueprint $table) {
            $table->id(); // INT PRIMARY KEY AUTO_INCREMENT [cite: 49, 94]
            $table->string('name'); // VARCHAR(255) NOT NULL, Tên danh mục sản phẩm [cite: 49, 94]
            $table->text('description')->nullable(); // TEXT, Mô tả danh mục [cite: 49, 94]
            $table->text('logo_url')->nullable(); // TEXT, Link ảnh/logo danh mục [cite: 49, 94]
            $table->timestamps(); // created_at DATETIME, updated_at DATETIME (auto) [cite: 49, 94]
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};