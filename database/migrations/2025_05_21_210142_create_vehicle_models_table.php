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
        Schema::create('vehicle_models', function (Blueprint $table) {
            $table->id(); // INT PRIMARY KEY AUTO_INCREMENT [cite: 55, 97]
            $table->foreignId('vehicle_brand_id')->constrained('vehicle_brands')->onDelete('cascade')->onUpdate('cascade'); // FK đến vehicle_brands(id) [cite: 55, 97]
            $table->string('name', 100); // VARCHAR(100) NOT NULL, Tên mẫu xe [cite: 55, 97]
            $table->integer('year')->nullable(); // INT, Năm sản xuất [cite: 55, 97]
            $table->text('description')->nullable(); // TEXT, Mô tả thêm [cite: 55, 97]
            $table->timestamps(); // created_at DATETIME, updated_at DATETIME (auto) [cite: 55, 97]
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_models');
    }
};