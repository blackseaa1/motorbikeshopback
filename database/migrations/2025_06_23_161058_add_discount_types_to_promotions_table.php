
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
            // Loại giảm giá: 'percentage' hoặc 'fixed'
            $table->string('discount_type', 20)->default('percentage')->after('discount_percentage');

            // Giá trị giảm giá cố định (nếu discount_type là 'fixed')
            $table->decimal('fixed_discount_amount', 10, 2)->nullable()->after('discount_type');

            // Giá trị giảm giá tối đa (nếu discount_type là 'percentage' và có giới hạn)
            $table->decimal('max_discount_amount', 10, 2)->nullable()->after('fixed_discount_amount');

            // Giá trị đơn hàng tối thiểu để áp dụng mã
            $table->decimal('min_order_amount', 10, 2)->nullable()->after('max_uses');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
            $table->dropColumn([
                'discount_type',
                'fixed_discount_amount',
                'max_discount_amount',
                'min_order_amount',
            ]);
        });
    }
};
