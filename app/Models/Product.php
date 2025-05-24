<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products'; // [cite: 60]

    protected $fillable = [
        'name', // [cite: 61]
        'description', // [cite: 61]
        'category_id', // [cite: 61]
        'brand_id', // [cite: 61]
        'price', // [cite: 61]
        'stock_quantity', // [cite: 61]
        'material', // [cite: 61]
        'color', // [cite: 61]
        'specifications', // [cite: 61]
        'is_active', // [cite: 61]
    ];

    protected $casts = [
        'price' => 'decimal:2', // [cite: 61]
        'stock_quantity' => 'integer', // [cite: 61]
        'is_active' => 'boolean', // [cite: 61]
    ];

    /**
     * Danh mục của sản phẩm.
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id'); // [cite: 61]
    }

    /**
     * Thương hiệu của sản phẩm.
     */
    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id'); // [cite: 61]
    }

    /**
     * Các hình ảnh của sản phẩm.
     */
    public function images()
    {
        return $this->hasMany(ProductImage::class, 'product_id'); // [cite: 63]
    }

    /**
     * Các mẫu xe tương thích với sản phẩm này.
     */
    public function vehicleModels()
    {
        return $this->belongsToMany(VehicleModel::class, 'product_vehicle_models', 'product_id', 'vehicle_model_id') // [cite: 65]
                    ->withTimestamps();
    }

    /**
     * Các chi tiết đơn hàng chứa sản phẩm này.
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'product_id'); // [cite: 69]
    }

    /**
     * Các đánh giá cho sản phẩm này.
     */
    public function reviews()
    {
        return $this->hasMany(Review::class, 'product_id'); // [cite: 71]
    }

    /**
     * Các mục trong giỏ hàng chứa sản phẩm này.
     */
    public function cartItems()
    {
        return $this->hasMany(CartItem::class, 'product_id'); // [cite: 75]
    }
}