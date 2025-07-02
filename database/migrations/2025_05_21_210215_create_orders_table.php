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
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->string('guest_name')->nullable();
            $table->string('guest_email')->nullable();
            $table->string('guest_phone', 100)->nullable();
            $table->foreignId('promotion_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->decimal('total_price', 10, 2)->nullable();
            $table->string('status', 100)->nullable();
            $table->foreignId('province_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('district_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('ward_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('delivery_service_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('created_by_admin_id')->nullable()->constrained('admins')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamps();
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
