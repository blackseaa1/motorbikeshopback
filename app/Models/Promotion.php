<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Promotion extends Model
{
    use HasFactory;

    protected $table = 'promotions';

    // Trạng thái cài đặt thủ công
    const STATUS_MANUAL_ACTIVE = 'active';
    const STATUS_MANUAL_INACTIVE = 'inactive';

    // Trạng thái hiệu lực (tính toán động)
    const STATUS_EFFECTIVE_ACTIVE = 'active';
    const STATUS_EFFECTIVE_SCHEDULED = 'scheduled';
    const STATUS_EFFECTIVE_EXPIRED = 'expired';
    const STATUS_EFFECTIVE_INACTIVE = 'inactive';

    // Loại giảm giá
    const DISCOUNT_TYPE_PERCENTAGE = 'percentage';
    const DISCOUNT_TYPE_FIXED = 'fixed';

    protected $fillable = [
        'code',
        'description',
        'discount_percentage',
        'discount_type', // Thêm trường mới
        'fixed_discount_amount', // Thêm trường mới
        'max_discount_amount', // Thêm trường mới
        'start_date',
        'end_date',
        'max_uses',
        'uses_count',
        'min_order_amount', // Thêm trường mới
        'status',
    ];

    protected $casts = [
        'discount_percentage' => 'decimal:2',
        'fixed_discount_amount' => 'decimal:2', // Thêm cast cho trường mới
        'max_discount_amount' => 'decimal:2', // Thêm cast cho trường mới
        'min_order_amount' => 'decimal:2', // Thêm cast cho trường mới
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'max_uses' => 'integer',
        'uses_count' => 'integer',
    ];

    protected $appends = [
        'effective_status_key',
        'effective_status_text',
        'effective_status_badge_class',
        'manual_status_text',
        'manual_status_badge_class',
        'formatted_discount',
        'display_discount_value', // Thêm thuộc tính mới để hiển thị
    ];

    //======================================================================
    // HELPER METHODS (CÁC HÀM KIỂM TRA)
    //======================================================================

    /**
     * Phương thức chính để kiểm tra xem mã có hợp lệ để áp dụng không.
     * Cần truyền giá trị subtotal của đơn hàng vào để kiểm tra.
     * @param float $subtotal
     */
    public function isEffective(float $subtotal = 0): bool
    {
        return $this->isManuallyActive() && $this->isCurrentlyActive() && $this->hasUsesLeft() && $this->meetsMinOrderAmount($subtotal);
    }

    /**
     * Kiểm tra xem mã có đang được bật thủ công không.
     */
    public function isManuallyActive(): bool
    {
        return $this->status === self::STATUS_MANUAL_ACTIVE;
    }

    /**
     * Kiểm tra xem mã có đang trong thời gian hiệu lực không.
     */
    public function isCurrentlyActive(): bool
    {
        $now = Carbon::now();
        $isStarted = $this->start_date === null || $now->gte($this->start_date);
        $isNotEnded = $this->end_date === null || $now->lte($this->end_date);
        return $isStarted && $isNotEnded;
    }

    /**
     * Kiểm tra xem mã có còn lượt sử dụng không.
     */
    public function hasUsesLeft(): bool
    {
        if ($this->max_uses === null) {
            return true; // Không giới hạn lượt sử dụng
        }
        return $this->uses_count < $this->max_uses;
    }

    /**
     * Kiểm tra xem đơn hàng có đạt giá trị tối thiểu để áp dụng khuyến mãi không.
     * @param float $subtotal
     */
    public function meetsMinOrderAmount(float $subtotal): bool
    {
        if ($this->min_order_amount === null) {
            return true; // Không yêu cầu giá trị đơn hàng tối thiểu
        }
        return $subtotal >= $this->min_order_amount;
    }

    //======================================================================
    // ACCESSORS (CÁC THUỘC TÍNH TỰ ĐỘNG)
    //======================================================================

    public function getEffectiveStatusKeyAttribute(): string
    {
        if (!$this->isManuallyActive()) {
            return self::STATUS_EFFECTIVE_INACTIVE;
        }
        if (!$this->isCurrentlyActive()) {
            $now = Carbon::now();
            return ($this->start_date && $now->lt($this->start_date)) ? self::STATUS_EFFECTIVE_SCHEDULED : self::STATUS_EFFECTIVE_EXPIRED;
        }
        // Để đơn giản, bỏ qua kiểm tra hasUsesLeft() ở đây để accessor này chỉ phản ánh trạng thái thời gian và trạng thái thủ công
        // Việc kiểm tra hasUsesLeft và min_order_amount sẽ được thực hiện đầy đủ trong hàm isEffective()
        return self::STATUS_EFFECTIVE_ACTIVE;
    }

    public function getEffectiveStatusTextAttribute(): string
    {
        return match ($this->effective_status_key) {
            self::STATUS_EFFECTIVE_ACTIVE => 'Đang hiệu lực',
            self::STATUS_EFFECTIVE_SCHEDULED => 'Chưa bắt đầu',
            self::STATUS_EFFECTIVE_EXPIRED => 'Hết hạn/Hết lượt', // Cần thêm logic để phân biệt hết hạn và hết lượt
            self::STATUS_EFFECTIVE_INACTIVE => 'Đã tắt',
            default => 'Không xác định',
        };
    }

    public function getEffectiveStatusBadgeClassAttribute(): string
    {
        return match ($this->effective_status_key) {
            self::STATUS_EFFECTIVE_ACTIVE => 'bg-success',
            self::STATUS_EFFECTIVE_SCHEDULED => 'bg-info text-dark',
            self::STATUS_EFFECTIVE_EXPIRED => 'bg-danger',
            self::STATUS_EFFECTIVE_INACTIVE => 'bg-secondary',
            default => 'bg-warning text-dark',
        };
    }

    public function getManualStatusTextAttribute(): string
    {
        return $this->status === self::STATUS_MANUAL_ACTIVE ? 'Đang bật' : 'Đang tắt';
    }

    public function getManualStatusBadgeClassAttribute(): string
    {
        return $this->status === self::STATUS_MANUAL_ACTIVE ? 'bg-success' : 'bg-secondary';
    }

    public function getFormattedDiscountAttribute(): string
    {
        // Hiển thị giá trị giảm giá theo loại
        if ($this->discount_type === self::DISCOUNT_TYPE_PERCENTAGE) {
            $formatted = rtrim(rtrim(number_format($this->discount_percentage, 2), '0'), '.') . '%';
            if ($this->max_discount_amount !== null) {
                $formatted .= ' (Tối đa ' . number_format($this->max_discount_amount) . 'đ)';
            }
            return $formatted;
        } elseif ($this->discount_type === self::DISCOUNT_TYPE_FIXED) {
            return number_format($this->fixed_discount_amount) . 'đ';
        }
        return 'N/A';
    }

    // Thêm accessor mới để hiển thị giá trị giảm giá một cách linh hoạt
    public function getDisplayDiscountValueAttribute(): string
    {
        $value = '';
        if ($this->discount_type === self::DISCOUNT_TYPE_PERCENTAGE) {
            $value = $this->discount_percentage . '%';
            if ($this->max_discount_amount !== null) {
                $value .= ' (Tối đa: ' . number_format($this->max_discount_amount) . 'đ)';
            }
        } elseif ($this->discount_type === self::DISCOUNT_TYPE_FIXED) {
            $value = number_format($this->fixed_discount_amount) . 'đ';
        }

        if ($this->min_order_amount !== null) {
            $value .= ' (Đơn hàng tối thiểu: ' . number_format($this->min_order_amount) . 'đ)';
        }
        return $value;
    }

    //======================================================================
    // RELATIONSHIPS
    //======================================================================

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Tính toán số tiền giảm giá thực tế dựa trên loại giảm giá và tổng phụ đơn hàng.
     * @param float $subtotal
     * @return float
     */
    public function calculateDiscount(float $subtotal): float
    {
        // Đảm bảo mã hợp lệ trước khi tính toán
        if (!$this->isEffective($subtotal)) {
            return 0;
        }

        $discount = 0;
        if ($this->discount_type === self::DISCOUNT_TYPE_PERCENTAGE) {
            $discount = ($subtotal * $this->discount_percentage) / 100;
            // Áp dụng giới hạn tối đa nếu có
            if ($this->max_discount_amount !== null && $discount > $this->max_discount_amount) {
                $discount = $this->max_discount_amount;
            }
        } elseif ($this->discount_type === self::DISCOUNT_TYPE_FIXED) {
            $discount = $this->fixed_discount_amount;
        }
        return round($discount, 2); // Làm tròn số tiền giảm giá
    }
}
