<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $table = 'carts'; //

    protected $fillable = [
        'customer_id', //
    ];

    /**
     * SỬA ĐỔI 1: Thêm mảng $appends.
     * Các accessor trong này sẽ được tự động thêm vào khi model chuyển thành JSON.
     * @var array
     */
    protected $appends = [
        'item_count',
        'total_price',
    ];

    //======================================================================
    // RELATIONSHIPS
    //======================================================================

    /**
     * Khách hàng sở hữu giỏ hàng này.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id'); //
    }

    /**
     * Các sản phẩm trong giỏ hàng (dưới dạng model CartItem).
     */
    public function items()
    {
        return $this->hasMany(CartItem::class, 'cart_id'); //
    }

    /**
     * Quan hệ nhiều-nhiều trực tiếp đến Product qua CartItem.
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'cart_items', 'cart_id', 'product_id')
            ->withPivot('quantity') //
            ->withTimestamps();
    }

    //======================================================================
    // ACCESSORS
    //======================================================================

    /**
     * SỬA ĐỔI 2: Accessor để lấy tổng số lượng sản phẩm trong giỏ hàng.
     *
     * @return int
     */
    public function getItemCountAttribute(): int
    {
        // Sử dụng eager loading `with('items')` trước khi gọi để tối ưu
        if (!$this->relationLoaded('items')) {
            $this->load('items');
        }
        return $this->items->sum('quantity');
    }

    /**
     * SỬA ĐỔI 3: Accessor để tính tổng giá trị của giỏ hàng.
     *
     * @return float
     */
    public function getTotalPriceAttribute(): float
    {
        // Sử dụng eager loading `with('items.product')` để tối ưu
        if (!$this->relationLoaded('items.product')) {
            $this->load('items.product');
        }

        return $this->items->reduce(function ($carry, $item) {
            // Kiểm tra $item->product tồn tại để tránh lỗi
            $price = $item->product ? $item->product->price : 0;
            return $carry + ($price * $item->quantity);
        }, 0);
    }
}
