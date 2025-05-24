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
        Schema::create('product_vehicle_models', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade')->onUpdate('cascade'); // FK đến products(id) [cite: 65, 103]
            $table->foreignId('vehicle_model_id')->constrained('vehicle_models')->onDelete('cascade')->onUpdate('cascade'); // FK đến vehicle_models(id) [cite: 65, 103]
            $table->timestamps(); // created_at DATETIME, updated_at DATETIME [cite: 65, 103]
            
            // Đảm bảo không bị trùng cặp sản phẩm - xe [cite: 65, 103]
            $table->primary(['product_id', 'vehicle_model_id']); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_vehicle_models');
    }
};