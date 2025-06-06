<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon; // Import Carbon

class Promotion extends Model
{
    use HasFactory;

    protected $table = 'promotions';

    // Định nghĩa các hằng số cho trạng thái cài đặt (manual status)
    const STATUS_ACTIVE = 'active';     // Người dùng cài đặt là active
    const STATUS_INACTIVE = 'inactive'; // Người dùng cài đặt là inactive

    // Các trạng thái hiển thị hiệu lực (effective status)
    const STATUS_EFFECTIVE_ACTIVE = 'active';      // Đang trong thời gian và được bật
    const STATUS_EFFECTIVE_SCHEDULED = 'scheduled';  // Chưa tới ngày bắt đầu nhưng được bật
    const STATUS_EFFECTIVE_EXPIRED = 'expired';    // Đã qua ngày kết thúc (dù được bật hay không)
    const STATUS_EFFECTIVE_INACTIVE = 'inactive';  // Bị tắt thủ công

    protected $fillable = [
        'code',
        'description', // Thêm description
        'discount_percentage',
        'start_date',
        'end_date',
        'max_uses',    // Số lượt sử dụng tối đa
        'uses_count',  // Số lượt đã sử dụng
        'status',      // Trạng thái cài đặt bởi người dùng (active/inactive)
    ];

    protected $casts = [
        'discount_percentage' => 'decimal:2',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'max_uses' => 'integer',
        'uses_count' => 'integer',
    ];

    /**
     * Các đơn hàng sử dụng mã khuyến mãi này.
     */
    public function orders()
    {
        return $this->hasMany(Order::class, 'promotion_id');
    }

    /**
     * Lấy trạng thái hiệu lực hiện tại của khuyến mãi.
     *
     * @return string
     */
    public function getEffectiveStatusKey()
    {
        $now = Carbon::now();
        if ($this->status === self::STATUS_INACTIVE) {
            return self::STATUS_EFFECTIVE_INACTIVE;
        }
        if ($this->end_date && $this->end_date < $now) {
            return self::STATUS_EFFECTIVE_EXPIRED;
        }
        if ($this->start_date && $this->start_date > $now) {
            return self::STATUS_EFFECTIVE_SCHEDULED;
        }
        // Nếu max_uses được đặt và uses_count >= max_uses
        if (isset($this->max_uses) && $this->uses_count >= $this->max_uses) {
            return self::STATUS_EFFECTIVE_EXPIRED; // Coi như hết hạn/hết lượt
        }
        return self::STATUS_EFFECTIVE_ACTIVE;
    }

    /**
     * Lấy text hiển thị cho trạng thái hiệu lực.
     *
     * @return string
     */
    public function getCurrentDisplayStatus()
    {
        switch ($this->getEffectiveStatusKey()) {
            case self::STATUS_EFFECTIVE_ACTIVE:
                return 'Đang có hiệu lực';
            case self::STATUS_EFFECTIVE_SCHEDULED:
                return 'Chưa bắt đầu';
            case self::STATUS_EFFECTIVE_EXPIRED:
                return 'Đã kết thúc/Hết lượt';
            case self::STATUS_EFFECTIVE_INACTIVE:
                return 'Đã tắt';
            default:
                return 'Không xác định';
        }
    }

    /**
     * Lấy class CSS (badge) cho trạng thái hiệu lực.
     *
     * @return string
     */
    public function getStatusBadgeClass()
    {
        switch ($this->getEffectiveStatusKey()) {
            case self::STATUS_EFFECTIVE_ACTIVE:
                return 'bg-success';
            case self::STATUS_EFFECTIVE_SCHEDULED:
                return 'bg-info text-dark';
            case self::STATUS_EFFECTIVE_EXPIRED:
                return 'bg-danger';
            case self::STATUS_EFFECTIVE_INACTIVE:
                return 'bg-secondary';
            default:
                return 'bg-warning text-dark';
        }
    }

    /**
     * Lấy text hiển thị cho trạng thái cài đặt (manual status).
     *
     * @return string
     */
    public function getConfigStatusText()
    {
        if ($this->status === self::STATUS_ACTIVE) {
            return 'Hoạt động (Bật)';
        }
        return 'Tạm tắt (Tắt)';
    }

    /**
     * Lấy class CSS (badge) cho trạng thái cài đặt.
     *
     * @return string
     */
    public function getConfigStatusBadgeClass()
    {
        if ($this->status === self::STATUS_ACTIVE) {
            return 'bg-success';
        }
        return 'bg-secondary';
    }


    /**
     * Kiểm tra xem trạng thái cài đặt có phải là active không.
     *
     * @return bool
     */
    public function isManuallyActive()
    {
        return $this->status === self::STATUS_ACTIVE;
    }
}
