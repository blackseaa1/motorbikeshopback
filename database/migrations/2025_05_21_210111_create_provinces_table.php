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
        Schema::create('provinces', function (Blueprint $table) {
            $table->id(); // INT AUTO_INCREMENT PRIMARY KEY [cite: 43, 91] (doc specifies INT, PK, SQL has AUTO_INCREMENT)
            $table->string('name'); // VARCHAR(255) NOT NULL [cite: 43, 91]
            $table->string('gso_id', 100)->nullable(); // VARCHAR(100) [cite: 43, 91]
            $table->timestamps(); // created_at DATETIME, updated_at DATETIME [cite: 43, 91]
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provinces');
    }
};