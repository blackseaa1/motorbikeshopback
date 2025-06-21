<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Number;
use App\Models\Admin; // SỬA ĐỔI: Thêm import Admin model

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';

    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_SHIPPED = 'shipped';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_RETURNED = 'returned';
    const STATUS_FAILED = 'failed';
    const STATUS_APPROVED = 'approved';

    protected $fillable = [
        'customer_id',
        'guest_name',
        'guest_email',
        'guest_phone',
        'promotion_id',
        'total_price',
        'status',
        'province_id',
        'district_id',
        'ward_id',
        'payment_method',
        'delivery_service_id',
        'notes',
        'created_by_admin_id',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
    ];

    protected $appends = [
        'status_text',
        'status_badge_class',
        'formatted_total_price',
        'customer_name',
        'full_address',
        'subtotal',
        'shipping_fee',
        'discount_amount',
    ];

    //======================================================================
    // RELATIONSHIPS
    //======================================================================

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function deliveryService()
    {
        return $this->belongsTo(DeliveryService::class, 'delivery_service_id');
    }

    public function promotion()
    {
        return $this->belongsTo(Promotion::class, 'promotion_id');
    }

    public function province()
    {
        return $this->belongsTo(Province::class, 'province_id');
    }

    public function district()
    {
        return $this->belongsTo(District::class, 'district_id');
    }

    public function ward()
    {
        return $this->belongsTo(Ward::class, 'ward_id');
    }

    /**
     * SỬA ĐỔI: Thêm mối quan hệ với Admin, người đã tạo hoặc xử lý đơn hàng.
     */
    public function createdByAdmin()
    {
        return $this->belongsTo(Admin::class, 'created_by_admin_id');
    }

    //======================================================================
    // ACCESSORS
    //======================================================================

    public function getStatusTextAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Chờ xử lý',
            self::STATUS_PROCESSING => 'Đang xử lý',
            self::STATUS_SHIPPED => 'Đã giao vận chuyển',
            self::STATUS_DELIVERED => 'Đã giao hàng',
            self::STATUS_COMPLETED => 'Đã hoàn thành',
            self::STATUS_CANCELLED => 'Đã hủy',
            self::STATUS_RETURNED => 'Đã trả hàng',
            self::STATUS_FAILED => 'Thất bại',
            self::STATUS_APPROVED => 'Đã duyệt',
            default => 'Không xác định',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'bg-warning text-dark',
            self::STATUS_PROCESSING => 'bg-info text-dark',
            self::STATUS_SHIPPED => 'bg-primary',
            self::STATUS_DELIVERED, self::STATUS_COMPLETED => 'bg-success',
            self::STATUS_CANCELLED, self::STATUS_RETURNED, self::STATUS_FAILED => 'bg-danger',
            self::STATUS_APPROVED => 'bg-success',
            default => 'bg-secondary',
        };
    }

    public function getFormattedTotalPriceAttribute(): string
    {
        return Number::currency($this->total_price, 'VND', 'vi');
    }

    public function getCustomerNameAttribute(): string
    {
        return ($this->customer_id && $this->customer)
            ? ($this->customer->full_name ?? '')
            : ($this->guest_name ?? '');
    }

    public function getFullAddressAttribute(): string
    {
        $addressParts = [];
        if ($this->ward) {
            $addressParts[] = $this->ward->name;
        }
        if ($this->district) {
            $addressParts[] = $this->district->name;
        }
        if ($this->province) {
            $addressParts[] = $this->province->name;
        }

        return implode(', ', array_filter($addressParts));
    }

    public function getSubtotalAttribute(): float
    {
        if (!$this->relationLoaded('items') || !$this->items->every(fn($item) => $item->relationLoaded('product'))) {
            $this->load('items.product');
        }
        return $this->items->sum(function ($item) {
            return $item->quantity * ($item->price ?? 0);
        });
    }

    public function getShippingFeeAttribute(): float
    {
        if (!$this->relationLoaded('deliveryService')) {
            $this->load('deliveryService');
        }
        return $this->deliveryService->shipping_fee ?? 0;
    }

    public function getDiscountAmountAttribute(): float
    {
        if (!$this->relationLoaded('promotion')) {
            $this->load('promotion');
        }
        $promotion = $this->promotion;
        $subtotal = $this->subtotal;
        if ($promotion && $promotion->isEffective()) {
            return ($subtotal * $promotion->discount_percentage) / 100;
        }
        return 0;
    }

    //======================================================================
    // NEW FUNCTIONALITY: CANCELLATION LOGIC
    //======================================================================

    public function isCancellable(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING,
            self::STATUS_PROCESSING,
        ]);
    }
}
