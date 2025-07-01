<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder; // Import Builder

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

    // Thêm các hằng số cho Filter/Sort options
    const FILTER_STATUS_ALL = 'all';
    const FILTER_STATUS_ACTIVE = 'active_effective';
    const FILTER_STATUS_INACTIVE = 'inactive_effective';
    const FILTER_STATUS_EXPIRED = 'expired_effective';
    const FILTER_STATUS_SCHEDULED = 'scheduled_effective';
    const FILTER_STATUS_MANUAL_ACTIVE = 'manual_active';
    const FILTER_STATUS_MANUAL_INACTIVE = 'manual_inactive';


    const FILTER_EXPIRY_ALL = 'all';
    const FILTER_EXPIRY_EXPIRING_SOON = 'expiring_soon'; // Sắp hết hạn (ví dụ: trong 7 ngày tới)
    const FILTER_EXPIRY_EXPIRED = 'expired'; // Đã hết hạn

    const FILTER_USAGE_ALL = 'all';
    const FILTER_USAGE_HIGHLY_USED = 'highly_used'; // Dùng nhiều (ví dụ: > 80% max_uses)
    const FILTER_USAGE_LOWLY_USED = 'lowly_used'; // Dùng ít (ví dụ: < 20% max_uses)
    const FILTER_USAGE_NO_USES = 'no_uses'; // Chưa dùng lần nào


    protected $fillable = [
        'code',
        'description',
        'discount_percentage',
        'discount_type',
        'fixed_discount_amount',
        'max_discount_amount',
        'start_date',
        'end_date',
        'max_uses',
        'uses_count',
        'min_order_amount',
        'status',
    ];

    protected $casts = [
        'discount_percentage' => 'decimal:2',
        'fixed_discount_amount' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
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
        'display_discount_value',
        'is_expiring_soon', // Thêm accessor mới
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
        return self::STATUS_EFFECTIVE_ACTIVE;
    }

    public function getEffectiveStatusTextAttribute(): string
    {
        // Kiểm tra thêm điều kiện hết lượt sử dụng để hiển thị rõ ràng hơn
        if ($this->effective_status_key === self::STATUS_EFFECTIVE_ACTIVE && !$this->hasUsesLeft()) {
            return 'Hết lượt sử dụng';
        }
        return match ($this->effective_status_key) {
            self::STATUS_EFFECTIVE_ACTIVE => 'Đang hiệu lực',
            self::STATUS_EFFECTIVE_SCHEDULED => 'Chưa bắt đầu',
            self::STATUS_EFFECTIVE_EXPIRED => 'Đã hết hạn',
            self::STATUS_EFFECTIVE_INACTIVE => 'Đã tắt',
            default => 'Không xác định',
        };
    }

    public function getEffectiveStatusBadgeClassAttribute(): string
    {
        // Kiểm tra thêm điều kiện hết lượt sử dụng
        if ($this->effective_status_key === self::STATUS_EFFECTIVE_ACTIVE && !$this->hasUsesLeft()) {
            return 'bg-warning text-dark'; // Hoặc bg-danger nếu muốn nổi bật hơn
        }
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
        if ($this->discount_type === self::DISCOUNT_TYPE_PERCENTAGE) {
            $formatted = rtrim(rtrim(number_format($this->discount_percentage, 2, ',', '.'), '0'), '.') . '%';
            if ($this->max_discount_amount !== null) {
                $formatted .= ' (Tối đa ' . number_format($this->max_discount_amount, 0, ',', '.') . 'đ)';
            }
            return $formatted;
        } elseif ($this->discount_type === self::DISCOUNT_TYPE_FIXED) {
            return number_format($this->fixed_discount_amount, 0, ',', '.') . 'đ';
        }
        return 'N/A';
    }

    public function getDisplayDiscountValueAttribute(): string
    {
        $value = '';
        if ($this->discount_type === self::DISCOUNT_TYPE_PERCENTAGE) {
            $value = rtrim(rtrim(number_format($this->discount_percentage, 2, ',', '.'), '0'), ',') . '%';
            if ($this->max_discount_amount !== null) {
                $value .= ' (Tối đa: ' . number_format($this->max_discount_amount, 0, ',', '.') . 'đ)';
            }
        } elseif ($this->discount_type === self::DISCOUNT_TYPE_FIXED) {
            $value = number_format($this->fixed_discount_amount, 0, ',', '.') . 'đ';
        }

        if ($this->min_order_amount !== null && $this->min_order_amount > 0) { // Chỉ hiển thị nếu có min_order_amount và > 0
            $value .= ' (Đơn hàng tối thiểu: ' . number_format($this->min_order_amount, 0, ',', '.') . 'đ)';
        }
        return $value;
    }

    /**
     * Accessor để kiểm tra xem mã có sắp hết hạn trong X ngày tới không (mặc định 7 ngày).
     * @return bool
     */
    public function getIsExpiringSoonAttribute(): bool
    {
        if (!$this->end_date) {
            return false;
        }
        $now = Carbon::now();
        $sevenDaysFromNow = $now->copy()->addDays(7);
        return $this->end_date->isBetween($now, $sevenDaysFromNow);
    }

    //======================================================================
    // LOCAL SCOPES (CHO VIỆC LỌC & SẮP XẾP)
    //======================================================================

    /**
     * Scope để lọc các mã đang hiệu lực (active).
     */
    public function scopeActiveEffective(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_MANUAL_ACTIVE)
            ->where('start_date', '<=', Carbon::now())
            ->where('end_date', '>=', Carbon::now())
            ->where(function ($q) {
                $q->whereNull('max_uses')
                    ->orWhereColumn('uses_count', '<', 'max_uses');
            });
    }

    /**
     * Scope để lọc các mã chưa bắt đầu (scheduled).
     */
    public function scopeScheduledEffective(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_MANUAL_ACTIVE)
            ->where('start_date', '>', Carbon::now());
    }

    /**
     * Scope để lọc các mã đã hết hạn (expired based on date or uses).
     */
    public function scopeExpiredEffective(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_MANUAL_ACTIVE)
            ->where(function ($q) {
                $q->where('end_date', '<', Carbon::now()) // Hết hạn theo ngày
                    ->orWhere(function ($q2) {
                        $q2->whereNotNull('max_uses')
                            ->whereColumn('uses_count', '>=', 'max_uses'); // Hết lượt sử dụng
                    });
            });
    }

    /**
     * Scope để lọc các mã đã bị tắt thủ công.
     */
    public function scopeInactiveEffective(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_MANUAL_INACTIVE);
    }

    /**
     * Scope để lọc các mã sắp hết hạn (trong X ngày tới).
     * @param Builder $query
     * @param int $days
     * @return Builder
     */
    public function scopeExpiringSoon(Builder $query, int $days = 7): Builder
    {
        $now = Carbon::now();
        $futureDate = $now->copy()->addDays($days);
        return $query->whereNotNull('end_date')
            ->where('end_date', '>', $now)
            ->where('end_date', '<=', $futureDate);
    }

    /**
     * Scope để lọc các mã đã hết hạn theo ngày.
     * @param Builder $query
     * @return Builder
     */
    public function scopeExpiredByDate(Builder $query): Builder
    {
        return $query->whereNotNull('end_date')->where('end_date', '<', Carbon::now());
    }

    /**
     * Scope để lọc các mã đã hết lượt sử dụng.
     * @param Builder $query
     * @return Builder
     */
    public function scopeUsesExhausted(Builder $query): Builder
    {
        return $query->whereNotNull('max_uses')->whereColumn('uses_count', '>=', 'max_uses');
    }

    /**
     * Scope để lọc các mã được sử dụng nhiều (ví dụ: uses_count / max_uses >= threshold).
     * @param Builder $query
     * @param float $threshold (ví dụ: 0.8 cho 80%)
     * @return Builder
     */
    public function scopeHighlyUsed(Builder $query, float $threshold = 0.8): Builder
    {
        return $query->whereNotNull('max_uses')
            ->whereRaw('uses_count / max_uses >= ?', [$threshold]);
    }

    /**
     * Scope để lọc các mã được sử dụng ít (ví dụ: uses_count / max_uses < threshold).
     * @param Builder $query
     * @param float $threshold (ví dụ: 0.2 cho 20%)
     * @return Builder
     */
    public function scopeLowlyUsed(Builder $query, float $threshold = 0.2): Builder
    {
        return $query->whereNotNull('max_uses')
            ->whereRaw('uses_count / max_uses < ?', [$threshold]);
    }

    /**
     * Scope để lọc các mã chưa được sử dụng lần nào.
     * @param Builder $query
     * @return Builder
     */
    public function scopeNoUses(Builder $query): Builder
    {
        return $query->where('uses_count', 0);
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
        if (!$this->isEffective($subtotal)) {
            return 0;
        }

        $discount = 0;
        if ($this->discount_type === self::DISCOUNT_TYPE_PERCENTAGE) {
            $discount = ($subtotal * $this->discount_percentage) / 100;
            if ($this->max_discount_amount !== null && $discount > $this->max_discount_amount) {
                $discount = $this->max_discount_amount;
            }
        } elseif ($this->discount_type === self::DISCOUNT_TYPE_FIXED) {
            $discount = $this->fixed_discount_amount;
        }
        return round($discount, 2);
    }
}
