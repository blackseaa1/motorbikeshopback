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
        Schema::create('carts', function (Blueprint $table) {
            $table->id(); // INT PRIMARY KEY AUTO_INCREMENT (SQL là AUTO_INCREMENT, docx là INT PRIMARY KEY) [cite: 73, 108]
            $table->foreignId('customer_id')->unique()->constrained('customers')->onDelete('cascade')->onUpdate('cascade'); // FK đến customers(id), UNIQUE [cite: 73, 108]
            $table->timestamps(); // created_at DATETIME, updated_at DATETIME [cite: 73, 108]
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};