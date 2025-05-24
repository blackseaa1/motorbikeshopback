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
        Schema::create('delivery_services', function (Blueprint $table) {
            $table->id(); // INT PRIMARY KEY AUTO_INCREMENT [cite: 59, 99]
            $table->string('name'); // VARCHAR(255) NOT NULL, Tên đơn vị giao hàng [cite: 59, 99]
            $table->text('logo_url')->nullable(); // TEXT, Link logo đơn vị giao hàng [cite: 59, 99]
            $table->decimal('shipping_fee', 10, 2)->default(0); // DECIMAL(10,2) NOT NULL DEFAULT 0, Phí giao hàng cố định (VNĐ) [cite: 59, 99]
            $table->timestamps(); // created_at DATETIME, updated_at DATETIME (auto) [cite: 59, 99]
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_services');
    }
};