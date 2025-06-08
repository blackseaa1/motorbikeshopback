<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class CartItem extends Pivot
{
    use HasFactory;

    protected $table = 'cart_items';
    public $incrementing = false; // Chính xác, vì không có cột id auto-increment

    protected $fillable = [
        'cart_id',
        'product_id',
        'quantity',
    ];

    /**
     * SỬA ĐỔI 1: Thêm mảng $appends.
     * @var array
     */
    protected $appends = ['subtotal'];

    protected $casts = [
        'quantity' => 'integer',
    ];

    //======================================================================
    // RELATIONSHIPS
    //======================================================================

    /**
     * Giỏ hàng mà mục này thuộc về.
     */
    public function cart()
    {
        return $this->belongsTo(Cart::class, 'cart_id');
    }

    /**
     * Sản phẩm trong mục giỏ hàng này.
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id'); //
    }

    //======================================================================
    // ACCESSORS
    //======================================================================

    /**
     * SỬA ĐỔI 2: Accessor để tính tổng tiền của một mục trong giỏ hàng.
     *
     * @return float
     */
    public function getSubtotalAttribute(): float
    {
        // Sử dụng eager loading `with('product')` để tối ưu
        if ($this->product) {
            return $this->product->price * $this->quantity;
        }
        return 0;
    }
}
