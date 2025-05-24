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
        Schema::create('brands', function (Blueprint $table) {
            $table->id(); // INT PRIMARY KEY AUTO_INCREMENT [cite: 51, 95]
            $table->string('name', 100); // VARCHAR(100) NOT NULL, Tên thương hiệu [cite: 51, 95]
            $table->text('description')->nullable(); // TEXT, Mô tả [cite: 51, 95]
            $table->text('logo_url')->nullable(); // TEXT, Link logo thương hiệu [cite: 51, 95]
            $table->timestamps(); // created_at DATETIME, updated_at DATETIME (auto) [cite: 51, 95]
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brands');
    }
};