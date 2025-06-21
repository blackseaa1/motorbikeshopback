<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory;

    // ... (các hằng số trạng thái giữ nguyên) ...
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_APPROVED = 'approved';
    const STATUS_SHIPPED = 'shipped';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_RETURNED = 'returned';
    const STATUS_FAILED = 'failed';

    const STATUSES = [
        self::STATUS_PENDING => 'Chờ xử lý',
        self::STATUS_PROCESSING => 'Đang xử lý',
        self::STATUS_APPROVED => 'Đã duyệt',
        self::STATUS_SHIPPED => 'Đã giao vận chuyển',
        self::STATUS_DELIVERED => 'Đã giao hàng',
        self::STATUS_COMPLETED => 'Hoàn thành',
        self::STATUS_CANCELLED => 'Đã hủy',
        self::STATUS_RETURNED => 'Đã trả hàng',
        self::STATUS_FAILED => 'Thất bại',
    ];

    protected $fillable = [
        'customer_id',
        'guest_name',
        'guest_email',
        'guest_phone',
        'shipping_address_line',
        'province_id',
        'district_id',
        'ward_id',
        'status',
        'total_price',
        'promotion_id',
        'delivery_service_id',
        'payment_method',
        'notes',
        'created_by_admin_id',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
    ];

    /** * SỬA LỖI: Thêm 'customer_name' vào mảng $appends.
     * Thêm các thuộc tính được tính toán vào JSON response.
     */
    protected $appends = [
        'status_text',
        'status_badge_class',
        'formatted_total_price',
        'full_address',
        'subtotal',
        'discount_amount',
        'formatted_discount',
        'customer_name' // Thêm dòng này
    ];


    // ... (Toàn bộ phần còn lại của file giữ nguyên không thay đổi) ...

    // Relationships
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    public function deliveryService(): BelongsTo
    {
        return $this->belongsTo(DeliveryService::class);
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function ward(): BelongsTo
    {
        return $this->belongsTo(Ward::class);
    }

    public function createdByAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by_admin_id');
    }

    // Accessors for computed properties

    public function getSubtotalAttribute()
    {
        return $this->items->sum(function ($item) {
            return $item->price * $item->quantity;
        });
    }

    public function getDiscountAmountAttribute()
    {
        if ($this->promotion) {
            return ($this->subtotal * $this->promotion->discount_percentage) / 100;
        }
        return 0;
    }

    public function getFormattedDiscountAttribute()
    {
        return number_format($this->discount_amount, 0, ',', '.') . ' ₫';
    }


    public function getStatusTextAttribute(): string
    {
        return self::STATUSES[$this->status] ?? 'Không xác định';
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return [
            self::STATUS_PENDING => 'bg-warning text-dark',
            self::STATUS_PROCESSING => 'bg-info text-dark',
            self::STATUS_APPROVED => 'bg-primary',
            self::STATUS_SHIPPED => 'bg-info',
            self::STATUS_DELIVERED => 'bg-light text-dark',
            self::STATUS_COMPLETED => 'bg-success',
            self::STATUS_CANCELLED => 'bg-secondary',
            self::STATUS_RETURNED => 'bg-dark',
            self::STATUS_FAILED => 'bg-danger',
        ][$this->status] ?? 'bg-light text-dark';
    }

    public function getFormattedTotalPriceAttribute(): string
    {
        return number_format($this->total_price, 0, ',', '.') . ' ₫';
    }

    public function getFullAddressAttribute(): string
    {
        if ($this->customer && $this->customer->defaultAddress) {
            return $this->customer->defaultAddress->full_address;
        }

        $addressParts = [
            $this->shipping_address_line,
            $this->ward?->name,
            $this->district?->name,
            $this->province?->name,
        ];

        return implode(', ', array_filter($addressParts));
    }

    public function getCustomerNameAttribute()
    {
        return $this->customer->name ?? $this->guest_name;
    }
}
