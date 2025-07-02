<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Number;
use Illuminate\Database\Eloquent\Builder; // Import Builder

class Product extends Model
{
    use HasFactory, SoftDeletes;

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

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
        'status' => 'string',
    ];

    protected $appends = [
        'formatted_price',
        'status_text',
        'status_badge_class',
        'thumbnail_url',
    ];

    //======================================================================
    // RELATIONSHIPS (SỬA ĐỔI: ĐIỀN ĐẦY ĐỦ CÁC QUAN HỆ)
    //======================================================================

    /**
     * Danh mục mà sản phẩm này thuộc về.
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
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    /**
     * THÊM HÀM NÀY
     * Định nghĩa quan hệ đặc biệt để lấy ra chỉ một ảnh đầu tiên (hoặc ảnh đại diện).
     * `ofMany('id', 'min')` sẽ lấy ảnh có ID nhỏ nhất làm ảnh đầu tiên.
     */
    public function firstImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->ofMany('id', 'min');
    }

    /**
     * Các dòng xe tương thích với sản phẩm (quan hệ nhiều-nhiều).
     */
    public function vehicleModels()
    {
        return $this->belongsToMany(VehicleModel::class, 'product_vehicle_models', 'product_id', 'vehicle_model_id');
    }

    /**
     * Các mục trong đơn hàng liên quan đến sản phẩm này.
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'product_id');
    }

    /**
     * Các đánh giá của sản phẩm.
     */
    public function reviews()
    {
        return $this->hasMany(Review::class, 'product_id');
    }

    /**
     * Các mục trong giỏ hàng liên quan đến sản phẩm này.
     */
    public function cartItems()
    {
        return $this->hasMany(CartItem::class, 'product_id');
    }

    //======================================================================
    // ACCESSORS (Bổ sung)
    //======================================================================

    public function getFormattedPriceAttribute(): string
    {
        return Number::currency($this->price, 'VND', 'vi');
    }

    public function getStatusTextAttribute(): string
    {
        return $this->status === self::STATUS_ACTIVE ? 'Đang bán' : 'Ngừng bán';
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return $this->status === self::STATUS_ACTIVE ? 'bg-success' : 'bg-danger';
    }

    public function getThumbnailUrlAttribute(): string
    {
        $firstImage = $this->relationLoaded('images')
            ? $this->images->first()
            : $this->images()->first();

        if ($firstImage) {
            return $firstImage->image_full_url;
        }

        return 'https://placehold.co/400x400/EFEFEF/AAAAAA&text=Product';
    }

    //======================================================================
    // SCOPES (Bổ sung)
    //======================================================================

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_INACTIVE);
    }

    public function scopeOutOfStock(Builder $query): Builder
    {
        return $query->where('stock_quantity', 0);
    }

    public function scopeLowStock(Builder $query, int $threshold = 10): Builder
    {
        return $query->where('stock_quantity', '>', 0)->where('stock_quantity', '<', $threshold);
    }
}