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
        Schema::create('promotions', function (Blueprint $table) {
            $table->id(); // INT PRIMARY KEY AUTO_INCREMENT [cite: 57, 98]
            $table->string('code', 50)->unique(); // VARCHAR(50) UNIQUE NOT NULL, Mã giảm giá [cite: 57, 98]
            $table->decimal('discount_percentage', 5, 2)->nullable(); // DECIMAL(5,2), Phần trăm giảm [cite: 57, 98]
            $table->dateTime('start_date')->nullable(); // DATETIME, Ngày bắt đầu [cite: 57, 98]
            $table->dateTime('end_date')->nullable(); // DATETIME, Ngày kết thúc [cite: 57, 98]
            $table->integer('usage_count')->default(0); // INT NOT NULL DEFAULT 0, Số lượt đã sử dụng [cite: 57, 98]
            $table->timestamps(); // created_at DATETIME, updated_at DATETIME [cite: 57, 98]
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};