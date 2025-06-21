<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Number; // SỬA ĐỔI: Thêm import Number
use App\Models\Admin; // SỬA ĐỔI: Thêm import Admin model

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders'; // SỬA ĐỔI: Thêm tên bảng

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
        'shipping_fee', // SỬA ĐỔI: Thêm shipping_fee
        'discount_amount',
        'customer_name'
    ];


    // ... (Toàn bộ phần còn lại của file giữ nguyên không thay đổi) ...

    // Relationships
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id'); // SỬA ĐỔI: Thêm foreign key
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id'); // SỬA ĐỔI: Thêm foreign key
    }

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class, 'promotion_id'); // SỬA ĐỔI: Thêm foreign key
    }

    public function deliveryService(): BelongsTo
    {
        return $this->belongsTo(DeliveryService::class, 'delivery_service_id'); // SỬA ĐỔI: Thêm foreign key
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'province_id'); // SỬA ĐỔI: Thêm foreign key
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class, 'district_id'); // SỬA ĐỔI: Thêm foreign key
    }

    public function ward(): BelongsTo
    {
        return $this->belongsTo(Ward::class, 'ward_id'); // SỬA ĐỔI: Thêm foreign key
    }

    public function createdByAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by_admin_id');
    }

    // Accessors for computed properties

    public function getSubtotalAttribute(): float
    {
        // SỬA ĐỔI: Thêm kiểm tra đã load relationship chưa
        if (!$this->relationLoaded('items')) {
            $this->load('items');
        }
        return $this->items->sum(function ($item) {
            return $item->price * $item->quantity;
        });
    }

    public function getDiscountAmountAttribute(): float
    {
        // SỬA ĐỔI: Thêm kiểm tra đã load relationship chưa và isEffective
        if (!$this->relationLoaded('promotion')) {
            $this->load('promotion');
        }
        $promotion = $this->promotion;
        $subtotal = $this->subtotal; // Sử dụng accessor subtotal
        if ($promotion && method_exists($promotion, 'isEffective') && $promotion->isEffective()) {
            return ($subtotal * $promotion->discount_percentage) / 100;
        }
        return 0;
    }

    // Removed getFormattedDiscountAttribute as it's not in the new appends array

    public function getStatusTextAttribute(): string
    {
        // SỬA ĐỔI: Chuyển sang match expression
        return match ($this->status) {
            self::STATUS_PENDING => 'Chờ xử lý',
            self::STATUS_PROCESSING => 'Đang xử lý',
            self::STATUS_APPROVED => 'Đã duyệt',
            self::STATUS_SHIPPED => 'Đã giao vận chuyển',
            self::STATUS_DELIVERED => 'Đã giao hàng',
            self::STATUS_COMPLETED => 'Hoàn thành',
            self::STATUS_CANCELLED => 'Đã hủy',
            self::STATUS_RETURNED => 'Đã trả hàng',
            self::STATUS_FAILED => 'Thất bại',
            default => 'Không xác định',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        // SỬA ĐỔI: Chuyển sang match expression và cập nhật class
        return match ($this->status) {
            self::STATUS_PENDING => 'bg-warning text-dark',
            self::STATUS_PROCESSING => 'bg-info text-dark',
            self::STATUS_APPROVED => 'bg-primary', // Giữ nguyên hoặc thay đổi tùy ý
            self::STATUS_SHIPPED => 'bg-info', // Giữ nguyên hoặc thay đổi tùy ý
            self::STATUS_DELIVERED, self::STATUS_COMPLETED => 'bg-success',
            self::STATUS_CANCELLED, self::STATUS_RETURNED, self::STATUS_FAILED => 'bg-danger',
            default => 'bg-light text-dark',
        };
    }

    public function getFormattedTotalPriceAttribute(): string
    {
        // SỬA ĐỔI: Sử dụng Number::currency
        return Number::currency($this->total_price, 'VND', 'vi');
    }

    public function getFullAddressAttribute(): string
    {
        // SỬA ĐỔI: Logic xây dựng địa chỉ
        $addressParts = [];
        if ($this->shipping_address_line) {
            $addressParts[] = $this->shipping_address_line;
        }
        if ($this->relationLoaded('ward') && $this->ward) {
            $addressParts[] = $this->ward->name;
        }
        if ($this->relationLoaded('district') && $this->district) {
            $addressParts[] = $this->district->name;
        }
        if ($this->relationLoaded('province') && $this->province) {
            $addressParts[] = $this->province->name;
        }

        return implode(', ', array_filter($addressParts));
    }

    public function getCustomerNameAttribute(): string
    {
        // SỬA ĐỔI: Logic lấy tên khách hàng
        return ($this->customer_id && $this->relationLoaded('customer') && $this->customer)
            ? ($this->customer->full_name ?? '')
            : ($this->guest_name ?? '');
    }

    public function getShippingFeeAttribute(): float
    {
        // SỬA ĐỔI: Thêm kiểm tra đã load relationship chưa
        if (!$this->relationLoaded('deliveryService')) {
            $this->load('deliveryService');
        }
        return $this->deliveryService->shipping_fee ?? 0;
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
