<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products'; 
    protected $fillable = [
        'name',
        'description',
        'category_id',
        'brand_id',
        'price',
        'stock_quantity',
        'material',
        'color',
        'specifications',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock_quantity' => 'integer',
    ];

    /**
     * Danh mục của sản phẩm.
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Thương hiệu của sản phẩm.
     */
    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    /**
     * Các hình ảnh của sản phẩm.
     */
    public function images()
    {
        return $this->hasMany(ProductImage::class, 'product_id'); 
    }

    /**
     * Các mẫu xe tương thích với sản phẩm này.
     */
    public function vehicleModels()
    {
        return $this->belongsToMany(VehicleModel::class, 'product_vehicle_models', 'product_id', 'vehicle_model_id')
            ->withTimestamps();
    }

    /**
     * Các chi tiết đơn hàng chứa sản phẩm này.
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'product_id'); 
    }

    /**
     * Các đánh giá cho sản phẩm này.
     */
    public function reviews()
    {
        return $this->hasMany(Review::class, 'product_id'); 
    }

    /**
     * Các mục trong giỏ hàng chứa sản phẩm này.
     */
    public function cartItems()
    {
        return $this->hasMany(CartItem::class, 'product_id'); 
    }
}
