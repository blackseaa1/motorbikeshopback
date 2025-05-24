<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot; // Thường dùng Pivot cho bảng trung gian của many-to-many

class CartItem extends Pivot
{
    use HasFactory;

    protected $table = 'cart_items'; // [cite: 74]
    public $incrementing = true; // Không có cột id auto-increment đơn lẻ
    // protected $primaryKey = ['cart_id', 'product_id']; // Laravel không hỗ trợ array primary key

    protected $fillable = [
        'cart_id', // [cite: 75]
        'product_id', // [cite: 75]
        'quantity', // [cite: 75]
    ];

    protected $casts = [
        'quantity' => 'integer', // [cite: 75]
    ];

    /**
     * Giỏ hàng mà mục này thuộc về.
     */
    public function cart()
    {
        return $this->belongsTo(Cart::class, 'cart_id'); // [cite: 75]
    }

    /**
     * Sản phẩm trong mục giỏ hàng này.
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id'); // [cite: 75]
    }
}