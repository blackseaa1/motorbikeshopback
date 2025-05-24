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
        Schema::create('admin', function (Blueprint $table) {
            $table->id(); // INT PRIMARY KEY AUTO_INCREMENT [cite: 39, 89]
            $table->string('name'); // VARCHAR(255) NOT NULL [cite: 39, 89]
            $table->string('email')->unique(); // VARCHAR(255) UNIQUE NOT NULL [cite: 39, 89]
            $table->string('phone'); // VARCHAR(255) NOT NULL (SQL schema has NOT NULL, docx doesn't specify for phone) [cite: 39, 89]
            $table->string('role', 50)->nullable(); // VARCHAR(50) [cite: 39, 89]
            $table->string('password'); // VARCHAR(255) NOT NULL [cite: 39, 89]
            $table->text('img')->nullable(); // TEXT, ảnh đại diện admin [cite: 39, 89]
            $table->timestamps(); // created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP [cite: 39, 89]
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin');
    }
};