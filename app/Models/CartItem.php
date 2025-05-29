<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot; // Thường dùng Pivot cho bảng trung gian của many-to-many

class CartItem extends Pivot
{
    use HasFactory;

    protected $table = 'cart_items'; 
    public $incrementing = false; // Không có cột id auto-increment đơn lẻ
    

    protected $fillable = [
        'cart_id', 
        'product_id', 
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'integer', 
    ];

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
        return $this->belongsTo(Product::class, 'product_id'); // [cite: 75]
    }
}