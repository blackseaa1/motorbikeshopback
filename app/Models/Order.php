<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Number; // Thêm use Number

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders'; //

    /**
     * SỬA ĐỔI 1: Định nghĩa các hằng số cho trạng thái đơn hàng.
     */
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_SHIPPED = 'shipped';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_RETURNED = 'returned';


    protected $fillable = [
        'customer_id', //
        'guest_name', //
        'guest_email', //
        'guest_phone', //
        'promotion_id', //
        'total_price', //
        'status', //
        'province_id', //
        'district_id', //
        'ward_id', //
        'payment_method', //
        'delivery_service_id', //
        'created_by_admin_id', //
    ];

    protected $casts = [
        'total_price' => 'decimal:2', //
    ];

    /**
     * SỬA ĐỔI 2: Thêm mảng $appends để tự động thêm các accessor vào JSON.
     * @var array
     */
    protected $appends = [
        'status_text',
        'status_badge_class',
        'formatted_total_price',
        'full_address',
        'customer_name',
    ];

    //======================================================================
    // RELATIONSHIPS (Không đổi)
    //======================================================================

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id'); //
    }

    public function promotion()
    {
        return $this->belongsTo(Promotion::class, 'promotion_id'); //
    }

    public function province()
    {
        return $this->belongsTo(Province::class, 'province_id'); //
    }

    public function district()
    {
        return $this->belongsTo(District::class, 'district_id'); //
    }

    public function ward()
    {
        return $this->belongsTo(Ward::class, 'ward_id'); //
    }

    public function deliveryService()
    {
        return $this->belongsTo(DeliveryService::class, 'delivery_service_id'); //
    }

    public function createdByAdmin()
    {
        return $this->belongsTo(Admin::class, 'created_by_admin_id'); //
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id'); //
    }

    //======================================================================
    // ACCESSORS (Bổ sung)
    //======================================================================

    /**
     * SỬA ĐỔI 3: Thêm các Accessor cho trạng thái.
     */
    public function getStatusTextAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Chờ xử lý',
            self::STATUS_PROCESSING => 'Đang xử lý',
            self::STATUS_SHIPPED => 'Đang giao hàng',
            self::STATUS_COMPLETED => 'Hoàn thành',
            self::STATUS_CANCELLED => 'Đã hủy',
            self::STATUS_RETURNED => 'Trả hàng',
            default => 'Không xác định',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'bg-warning text-dark',
            self::STATUS_PROCESSING => 'bg-info text-dark',
            self::STATUS_SHIPPED => 'bg-primary',
            self::STATUS_COMPLETED => 'bg-success',
            self::STATUS_CANCELLED, self::STATUS_RETURNED => 'bg-danger',
            default => 'bg-secondary',
        };
    }

    /**
     * SỬA ĐỔI 4: Thêm Accessor định dạng tiền tệ.
     */
    public function getFormattedTotalPriceAttribute(): string
    {
        return Number::currency($this->total_price, 'VND', 'vi');
    }

    /**
     * SỬA ĐỔI 5: Thêm Accessor lấy địa chỉ đầy đủ.
     */
    public function getFullAddressAttribute(): string
    {
        // Để tối ưu, hãy eager load: Order::with(['ward', 'district', 'province'])->find(1);
        $addressParts = [
            $this->ward?->name,
            $this->district?->name,
            $this->province?->name,
        ];
        // Lọc bỏ các phần tử null và nối chuỗi
        return implode(', ', array_filter($addressParts));
    }

    /**
     * SỬA ĐỔI 6: Thêm Accessor lấy tên khách hàng thống nhất.
     */
    public function getCustomerNameAttribute(): string
    {
        return $this->customer ? $this->customer->name : ($this->guest_name ?? 'Khách vãng lai');
    }
}
