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
        Schema::create('reviews', function (Blueprint $table) {
            // Khóa ngoại tới bảng customers
            $table->foreignId('customer_id')
                ->constrained('customers')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            // Khóa ngoại tới bảng products
            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->integer('rating')->nullable()->comment('Đánh giá từ 1 đến 5 sao');
            $table->text('comment')->nullable();
            // Đã sửa: Thay đổi giá trị mặc định của status từ 'pending_approval' thành 'pending'
            $table->string('status', 50)->default('pending'); // 'pending', 'approved', 'rejected'
            $table->timestamps();

            // Thiết lập khóa chính phức hợp: mỗi khách hàng chỉ được review 1 sản phẩm 1 lần
            $table->primary(['customer_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
