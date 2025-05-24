<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders'; // [cite: 66]

    protected $fillable = [
        'customer_id', // [cite: 67]
        'guest_name', // [cite: 67]
        'guest_email', // [cite: 67]
        'guest_phone', // [cite: 67]
        'promotion_id', // [cite: 67]
        'total_price', // [cite: 67]
        'status', // [cite: 67]
        'province_id', // [cite: 67]
        'district_id', // [cite: 67]
        'ward_id', // [cite: 67]
        'payment_method', // [cite: 67]
        'delivery_service_id', // [cite: 67]
        'created_by_admin_id', // [cite: 67]
    ];

    protected $casts = [
        'total_price' => 'decimal:2', // [cite: 67]
    ];

    /**
     * Khách hàng (nếu có) đã đặt đơn hàng này.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id'); // [cite: 67]
    }

    /**
     * Mã khuyến mãi (nếu có) được áp dụng cho đơn hàng.
     */
    public function promotion()
    {
        return $this->belongsTo(Promotion::class, 'promotion_id'); // [cite: 67]
    }

    /**
     * Tỉnh/thành giao hàng.
     */
    public function province()
    {
        return $this->belongsTo(Province::class, 'province_id'); // [cite: 67]
    }

    /**
     * Quận/huyện giao hàng.
     */
    public function district()
    {
        return $this->belongsTo(District::class, 'district_id'); // [cite: 67]
    }

    /**
     * Phường/xã giao hàng.
     */
    public function ward()
    {
        return $this->belongsTo(Ward::class, 'ward_id'); // [cite: 67]
    }

    /**
     * Dịch vụ giao hàng được sử dụng.
     */
    public function deliveryService()
    {
        return $this->belongsTo(DeliveryService::class, 'delivery_service_id'); // [cite: 67]
    }

    /**
     * Admin (nếu có) đã tạo đơn hàng này.
     */
    public function createdByAdmin()
    {
        return $this->belongsTo(Admin::class, 'created_by_admin_id'); // [cite: 67]
    }


    /**
     * Các chi tiết (sản phẩm) trong đơn hàng.
     */
    public function items() // Hoặc orderItems()
    {
        return $this->hasMany(OrderItem::class, 'order_id'); // [cite: 69]
    }
}
