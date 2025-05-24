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
        Schema::create('districts', function (Blueprint $table) {
            $table->id(); // INT AUTO_INCREMENT PRIMARY KEY [cite: 45, 92] (doc specifies INT, PK, SQL has AUTO_INCREMENT)
            $table->string('name'); // VARCHAR(255) NOT NULL [cite: 45, 92]
            $table->string('gso_id', 100)->nullable(); // VARCHAR(100) [cite: 45, 92]
            $table->foreignId('province_id')->constrained('provinces')->onDelete('cascade')->onUpdate('cascade'); // FK đến provinces(id) [cite: 45, 92]
            $table->timestamps(); // created_at DATETIME, updated_at DATETIME [cite: 45, 92]
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('districts');
    }
};