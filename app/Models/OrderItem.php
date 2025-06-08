<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Number; // Thêm use Number

class OrderItem extends Pivot
{
    use HasFactory;

    protected $table = 'order_items';
    public $incrementing = false;

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'price', // Giá tại thời điểm đặt hàng
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2',
    ];

    /**
     * SỬA ĐỔI 1: Thêm mảng $appends.
     * @var array
     */
    protected $appends = [
        'subtotal',
        'formatted_price',
        'formatted_subtotal'
    ];


    //======================================================================
    // RELATIONSHIPS (Không đổi)
    //======================================================================

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    //======================================================================
    // ACCESSORS (Bổ sung)
    //======================================================================

    /**
     * SỬA ĐỔI 2: Accessor tính tổng tiền cho mục này.
     */
    public function getSubtotalAttribute(): float
    {
        return $this->price * $this->quantity;
    }

    /**
     * SỬA ĐỔI 3: Accessor định dạng đơn giá.
     */
    public function getFormattedPriceAttribute(): string
    {
        return Number::currency($this->price, 'VND', 'vi');
    }

    /**
     * SỬA ĐỔI 4: Accessor định dạng tổng tiền của mục này.
     */
    public function getFormattedSubtotalAttribute(): string
    {
        return Number::currency($this->getSubtotalAttribute(), 'VND', 'vi');
    }
}
