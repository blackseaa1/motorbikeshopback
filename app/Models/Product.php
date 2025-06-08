<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Number; // Thêm use Number

class Product extends Model
{
    use HasFactory;

    /**
     * SỬA ĐỔI 1: Thêm hằng số cho trạng thái.
     */
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

    /**
     * SỬA ĐỔI 2: Thêm $appends để tự động thêm các accessor vào JSON.
     * @var array
     */
    protected $appends = [
        'formatted_price',
        'status_text',
        'status_badge_class',
        'thumbnail_url',
    ];

    //======================================================================
    // RELATIONSHIPS (Không đổi)
    //======================================================================
    public function category()
    { /*...*/
    }
    public function brand()
    { /*...*/
    }
    public function images()
    { /*...*/
    }
    public function vehicleModels()
    { /*...*/
    }
    public function orderItems()
    { /*...*/
    }
    public function reviews()
    { /*...*/
    }
    public function cartItems()
    { /*...*/
    }

    //======================================================================
    // ACCESSORS (Bổ sung)
    //======================================================================

    /**
     * SỬA ĐỔI 3: Thêm các Accessor cần thiết.
     */
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
        // Để tối ưu, hãy eager load: Product::with('images')->find(1);
        if ($this->images->isNotEmpty()) {
            // Lấy URL đầy đủ từ accessor của ProductImage
            return $this->images->first()->image_full_url;
        }
        return 'https://placehold.co/400x400/EFEFEF/AAAAAA&text=Product';
    }

    //======================================================================
    // SCOPES (Bổ sung)
    //======================================================================

    /**
     * SỬA ĐỔI 4: Thêm scope cho trạng thái active.
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }
}
