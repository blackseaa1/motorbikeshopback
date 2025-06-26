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
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Tên hiển thị (VD: Thanh toán khi nhận hàng, Ví MoMo)
            $table->string('code')->unique(); // Mã để xử lý logic (VD: cod, momo, vnpay)
            $table->text('description')->nullable(); // Mô tả ngắn về phương thức
            $table->string('logo_path')->nullable(); // Đường dẫn đến file logo
            $table->string('status', 50)->default('active');
            $table->timestamps(); // Tự động tạo 2 cột created_at và updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
