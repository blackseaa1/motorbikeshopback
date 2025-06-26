<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_saved_payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('payment_method_id')->constrained('payment_methods')->onDelete('cascade');
            // Thêm các cột để lưu thông tin chi tiết nếu cần, ví dụ:
            // $table->string('card_last_four')->nullable();
            // $table->string('card_holder_name')->nullable();
            $table->boolean('is_default')->default(false); // Đánh dấu đây có phải là phương thức mặc định không
            $table->timestamps();

            // Đảm bảo một khách hàng không lưu cùng một phương thức nhiều lần
            // Dòng code mới đã được sửa
            $table->unique(['customer_id', 'payment_method_id'], 'customer_payment_method_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_saved_payment_methods');
    }
};
