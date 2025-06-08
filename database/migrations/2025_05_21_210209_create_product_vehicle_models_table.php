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
            $table->foreignId('product_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('vehicle_model_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            // Set Primary Key
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
