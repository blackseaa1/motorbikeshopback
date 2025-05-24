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
        Schema::create('wards', function (Blueprint $table) {
            $table->id(); // INT AUTO_INCREMENT PRIMARY KEY [cite: 47, 93] (doc specifies INT, PK, SQL has AUTO_INCREMENT)
            $table->string('name'); // VARCHAR(255) NOT NULL [cite: 47, 93]
            $table->string('gso_id', 100)->nullable(); // VARCHAR(100) [cite: 47, 93]
            $table->foreignId('district_id')->constrained('districts')->onDelete('cascade')->onUpdate('cascade'); // FK đến districts(id) [cite: 47, 93]
            $table->timestamps(); // created_at DATETIME, updated_at DATETIME [cite: 47, 93]
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wards');
    }
};