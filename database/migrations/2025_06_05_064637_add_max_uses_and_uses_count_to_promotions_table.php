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
        Schema::table('promotions', function (Blueprint $table) {
            // Thêm cột max_uses sau cột end_date (hoặc vị trí phù hợp khác)
            $table->unsignedInteger('max_uses')->nullable()->after('end_date'); // Số lượt sử dụng tối đa, cho phép null

            // Thêm cột uses_count sau cột max_uses
            $table->unsignedInteger('uses_count')->default(0)->after('max_uses'); // Số lượt đã sử dụng, mặc định là 0
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
            $table->dropColumn(['max_uses', 'uses_count']); // Để có thể rollback nếu cần
        });
    }
};
