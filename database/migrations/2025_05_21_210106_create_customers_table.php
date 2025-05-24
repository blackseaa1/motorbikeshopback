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
        Schema::create('customers', function (Blueprint $table) {
            $table->id(); // INT PRIMARY KEY AUTO_INCREMENT [cite: 41, 90]
            $table->string('name'); // VARCHAR(255) NOT NULL [cite: 41, 90]
            $table->string('email')->unique(); // VARCHAR(255) UNIQUE NOT NULL [cite: 41, 90]
            $table->string('password'); // VARCHAR(255) NOT NULL [cite: 41, 90]
            $table->string('phone')->nullable(); // VARCHAR(255) [cite: 41, 90]
            $table->text('img')->nullable(); // TEXT, ảnh đại diện khách hàng [cite: 41, 90]
            $table->timestamps(); // created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP [cite: 41, 90]
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};