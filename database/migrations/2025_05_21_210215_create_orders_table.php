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
        Schema::create('orders', function (Blueprint $table) {
            $table->id(); // INT PRIMARY KEY AUTO_INCREMENT [cite: 67, 104]
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null')->onUpdate('cascade'); // FK đến customers(id) [cite: 67, 105]
            $table->string('guest_name')->nullable(); // Tên người đặt (nếu không đăng nhập) [cite: 67, 104]
            $table->string('guest_email')->nullable(); // Email người đặt (khách) [cite: 67, 104]
            $table->string('guest_phone', 100)->nullable(); // SĐT người đặt (khách) [cite: 67, 104]
            $table->foreignId('promotion_id')->nullable()->constrained('promotions')->onDelete('set null')->onUpdate('cascade'); // FK đến promotions(id) [cite: 67, 105]
            $table->decimal('total_price', 10, 2)->nullable(); // Tổng giá đơn hàng [cite: 67, 104]
            $table->string('status', 100)->nullable(); // Trạng thái đơn hàng (pending, delivered,...) [cite: 67, 104]
            
            $table->foreignId('province_id')->nullable()->constrained('provinces')->onDelete('set null')->onUpdate('cascade'); // Địa chỉ giao (FK đến provinces(id)) - SQL gốc không có ON DELETE/UPDATE, SET NULL là an toàn [cite: 67, 105]
            $table->foreignId('district_id')->nullable()->constrained('districts')->onDelete('set null')->onUpdate('cascade'); // FK đến districts(id) - SQL gốc không có ON DELETE/UPDATE, SET NULL là an toàn [cite: 67, 105]
            $table->foreignId('ward_id')->nullable()->constrained('wards')->onDelete('set null')->onUpdate('cascade'); // FK đến wards(id) - SQL gốc không có ON DELETE/UPDATE, SET NULL là an toàn [cite: 67, 105]

            $table->string('payment_method', 100)->nullable(); // Phương thức thanh toán [cite: 67, 104]
            $table->foreignId('delivery_service_id')->nullable()->constrained('delivery_services')->onDelete('set null')->onUpdate('cascade'); // FK đến delivery_services(id) - SQL gốc không có ON DELETE/UPDATE, SET NULL là an toàn [cite: 67, 105]
            
            $table->foreignId('created_by_admin_id')->nullable()->constrained('admin')->onDelete('set null')->onUpdate('cascade'); // Ai tạo đơn (Admin) [cite: 67, 105]
            
            $table->timestamps(); // created_at DATETIME, updated_at DATETIME [cite: 67, 104, 105]
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};