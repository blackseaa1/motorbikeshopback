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

    protected $fillable = [
        'code',
        'description',
        'discount_percentage',
        'start_date',
        'end_date',
        'max_uses',
        'uses_count',
        'status',
    ];

    protected $casts = [
        'discount_percentage' => 'decimal:2',
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
    ];

    //======================================================================
    // HELPER METHODS (CÁC HÀM KIỂM TRA) - PHẦN SỬA LỖI
    //======================================================================

    /**
     * Phương thức chính để kiểm tra xem mã có hợp lệ để áp dụng không.
     * Đây là phương thức đang bị thiếu trong file của bạn.
     */
    public function isEffective(): bool
    {
        return $this->isManuallyActive() && $this->isCurrentlyActive() && $this->hasUsesLeft();
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
        if (!$this->hasUsesLeft()) {
            return self::STATUS_EFFECTIVE_EXPIRED;
        }
        return self::STATUS_EFFECTIVE_ACTIVE;
    }

    public function getEffectiveStatusTextAttribute(): string
    {
        return match ($this->effective_status_key) {
            self::STATUS_EFFECTIVE_ACTIVE => 'Đang hiệu lực',
            self::STATUS_EFFECTIVE_SCHEDULED => 'Chưa bắt đầu',
            self::STATUS_EFFECTIVE_EXPIRED => 'Hết hạn/Hết lượt',
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
        return rtrim(rtrim(number_format($this->discount_percentage, 2), '0'), '.') . '%';
    }

    //======================================================================
    // RELATIONSHIPS
    //======================================================================

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
