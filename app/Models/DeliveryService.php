<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Number;

class DeliveryService extends Model
{
    use HasFactory;

    protected $table = 'delivery_services';

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'name',
        'logo_url',
        'shipping_fee',
        'status',
    ];

    protected $casts = [
        'shipping_fee' => 'decimal:2',
    ];

    protected $appends = ['logo_full_url', 'status_text', 'status_badge_class', 'formatted_shipping_fee'];

    //======================================================================
    // RELATIONSHIPS
    //======================================================================

    public function orders()
    {
        return $this->hasMany(Order::class, 'delivery_service_id');
    }

    //======================================================================
    // ACCESSORS
    //======================================================================

    /**
     * Accessor cho URL đầy đủ của logo.
     * Thêm timestamp để tránh cache trình duyệt.
     */
    public function getLogoFullUrlAttribute(): string
    {
        // ĐÃ SỬA: Sử dụng data_get để truy cập an toàn, tránh lỗi Undefined array key
        $logoPath = data_get($this->attributes, 'logo_url');

        if ($logoPath && Storage::disk('public')->exists($logoPath)) {
            // Thêm timestamp của updated_at để cache-busting
            return Storage::url($logoPath) . '?v=' . ($this->updated_at ? $this->updated_at->timestamp : time());
        }
        return 'https://placehold.co/150x50/EFEFEF/AAAAAA&text=Logo';
    }

    /**
     * Accessor cho hiển thị trạng thái.
     */
    public function getStatusTextAttribute(): string
    {
        return $this->isActive() ? 'Hoạt động' : 'Đã ẩn';
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return $this->isActive() ? 'bg-success' : 'bg-secondary';
    }

    /**
     * Accessor để định dạng phí vận chuyển.
     */
    public function getFormattedShippingFeeAttribute(): string
    {
        return Number::currency($this->shipping_fee, 'VND', 'vi');
    }

    //======================================================================
    // HELPERS
    //======================================================================

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }
}
