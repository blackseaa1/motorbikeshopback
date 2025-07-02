<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Number;
use App\Models\Admin;
use App\Models\PaymentMethod;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';

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
        'payment_method_id',
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
        'notes',
        'created_by_admin_id',
        'subtotal',
        'shipping_fee',
        'discount_amount',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
    ];

    protected $appends = [
        'status_text',
        'status_badge_class',
        'formatted_total_price',
        'full_address',
        'subtotal',
        'shipping_fee',
        'discount_amount',
        'customer_name',
        'formatted_id', // THÊM DÒNG NÀY: Để tự động thêm 'formatted_id' vào đối tượng Order
    ];

    // Relationships
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class, 'promotion_id');
    }

    public function deliveryService(): BelongsTo
    {
        return $this->belongsTo(DeliveryService::class, 'delivery_service_id');
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'province_id');
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class, 'district_id');
    }

    public function ward(): BelongsTo
    {
        return $this->belongsTo(Ward::class, 'ward_id');
    }

    public function createdByAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by_admin_id');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    // Accessors for computed properties

    /**
     * Accessor để định dạng ID thành 6 chữ số có số 0 ở đầu.
     */
    public function getFormattedIdAttribute(): string
    {
        return str_pad($this->id, 6, '0', STR_PAD_LEFT);
    }

    public function getSubtotalAttribute(): float
    {
        if (!$this->relationLoaded('items')) {
            $this->load('items');
        }
        return $this->items->sum(function ($item) {
            return $item->price * $item->quantity;
        });
    }

    public function getDiscountAmountAttribute(): float
    {
        if (!$this->relationLoaded('promotion')) {
            $this->load('promotion');
        }
        $promotion = $this->promotion;
        $subtotal = $this->subtotal;
        if ($promotion) {
            // Sử dụng phương thức calculateDiscount từ mô hình Promotion
            return $promotion->calculateDiscount($subtotal);
        }
        return 0;
    }

    public function getStatusTextAttribute(): string
    {
        // Sử dụng mảng STATUSES đã định nghĩa để lấy tên trạng thái thân thiện
        return self::STATUSES[$this->status] ?? 'Không xác định';
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'bg-warning text-dark',
            self::STATUS_PROCESSING => 'bg-info text-dark',
            self::STATUS_APPROVED => 'bg-primary',
            self::STATUS_SHIPPED => 'bg-info',
            self::STATUS_DELIVERED, self::STATUS_COMPLETED => 'bg-success',
            self::STATUS_CANCELLED, self::STATUS_RETURNED, self::STATUS_FAILED => 'bg-danger',
            default => 'bg-light text-dark',
        };
    }

    public function getFormattedTotalPriceAttribute(): string
    {
        return Number::currency($this->total_price, 'VND', 'vi');
    }

    public function getFullAddressAttribute(): string
    {
        $addressParts = [];
        if ($this->shipping_address_line) {
            $addressParts[] = $this->shipping_address_line;
        }
        // Kiểm tra xem mối quan hệ có được tải không trước khi truy cập
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
        return ($this->customer_id && $this->relationLoaded('customer') && $this->customer)
            ? ($this->customer->name ?? '')
            : ($this->guest_name ?? '');
    }

    public function getShippingFeeAttribute(): float
    {
        return 0.00;
    }

    public function isCancellable(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING,
            self::STATUS_PROCESSING,
        ]);
    }

    public function isRetriable(): bool
    {
        if (!$this->relationLoaded('paymentMethod')) {
            $this->load('paymentMethod');
        }

        $isRetriableStatus = in_array($this->status, [
            self::STATUS_PENDING,
            self::STATUS_FAILED,
            self::STATUS_CANCELLED,
        ]);

        $isOnlinePayment = false;
        if ($this->paymentMethod) {
            $onlinePaymentMethods = ['momo', 'vnpay'];
            $isOnlinePayment = in_array($this->paymentMethod->code, $onlinePaymentMethods);
        }

        return $isRetriableStatus && $isOnlinePayment;
    }
}
