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
    const STATUS_EFFECTIVE_INACTIVE = 'inactive'; // Bị tắt thủ công

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

    /**
     * SỬA ĐỔI 1: Thêm $appends để tự động thêm các accessor vào JSON.
     * @var array
     */
    protected $appends = [
        'effective_status_key',
        'effective_status_text',
        'effective_status_badge_class',
        'manual_status_text',
        'manual_status_badge_class',
        'formatted_discount'
    ];


    public function orders()
    {
        return $this->hasMany(Order::class, 'promotion_id');
    }

    /**
     * SỬA ĐỔI 2: Chuyển các phương thức helper thành Accessor.
     */
    public function getEffectiveStatusKeyAttribute(): string
    {
        if ($this->status === self::STATUS_MANUAL_INACTIVE) {
            return self::STATUS_EFFECTIVE_INACTIVE;
        }
        if ($this->end_date && $this->end_date < Carbon::now()) {
            return self::STATUS_EFFECTIVE_EXPIRED;
        }
        if (isset($this->max_uses) && $this->uses_count >= $this->max_uses) {
            return self::STATUS_EFFECTIVE_EXPIRED;
        }
        if ($this->start_date && $this->start_date > Carbon::now()) {
            return self::STATUS_EFFECTIVE_SCHEDULED;
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

    public function isManuallyActive(): bool
    {
        return $this->status === self::STATUS_MANUAL_ACTIVE;
    }
}
