<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $table = 'carts'; // [cite: 72]

    protected $fillable = [
        'customer_id', // [cite: 73]
    ];

    /**
     * Khách hàng sở hữu giỏ hàng này.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id'); // [cite: 73]
    }

    /**
     * Các sản phẩm trong giỏ hàng.
     */
    public function items() // Hoặc cartItems()
    {
        return $this->hasMany(CartItem::class, 'cart_id'); // [cite: 75]
    }

    // Nếu bạn muốn định nghĩa quan hệ nhiều-nhiều trực tiếp đến Product qua CartItem:
    public function products()
    {
        return $this->belongsToMany(Product::class, 'cart_items', 'cart_id', 'product_id')
                    ->withPivot('quantity') // [cite: 75]
                    ->withTimestamps();
    }
}