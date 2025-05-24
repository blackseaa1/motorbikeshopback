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
        Schema::create('vehicle_brands', function (Blueprint $table) {
            $table->id(); // INT PRIMARY KEY AUTO_INCREMENT [cite: 53, 96]
            $table->string('name', 100); // VARCHAR(100) NOT NULL, Tên hãng xe [cite: 53, 96]
            $table->text('description')->nullable(); // TEXT, Mô tả [cite: 53, 96]
            $table->text('logo_url')->nullable(); // TEXT, Link logo hãng xe [cite: 53, 96]
            $table->timestamps(); // created_at DATETIME, updated_at DATETIME (auto) [cite: 53, 96]
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_brands');
    }
};