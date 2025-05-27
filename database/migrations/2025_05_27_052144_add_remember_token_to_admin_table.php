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
        Schema::table('admin', function (Blueprint $table) {
            // Thêm dòng này để tạo cột remember_token
            $table->rememberToken();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admin', function (Blueprint $table) {
            // Thêm dòng này để có thể rollback migration nếu cần
            $table->dropRememberToken();
        });
    }
};
