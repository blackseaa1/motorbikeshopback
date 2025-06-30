<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $table = 'reviews';

    /**
     * SỬA ĐỔI 1: Bỏ các khai báo khóa chính phức hợp để dùng id tự tăng.
     */
    // public $incrementing = false; // Bỏ dòng này

    /**
     * SỬA ĐỔI 2: Định nghĩa các hằng số cho trạng thái.
     */
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    /**
     * Thêm hằng số STATUSES kiểu mảng để sửa lỗi "Undefined constant".
     */
    public const STATUSES = [
        self::STATUS_PENDING => 'Chờ duyệt',
        self::STATUS_APPROVED => 'Đã duyệt',
        self::STATUS_REJECTED => 'Từ chối',
    ];

    protected $fillable = [
        'customer_id',
        'product_id',
        'rating',
        'comment',
        'status',
    ];

    /**
     * SỬA ĐỔI 3: Thêm $appends.
     * @var array
     */
    protected $appends = ['status_text', 'status_badge_class'];

    protected $casts = [
        'rating' => 'integer',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * SỬA ĐỔI 4: Thêm các Accessor cho trạng thái.
     */
    public function getStatusTextAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Chờ duyệt',
            self::STATUS_APPROVED => 'Đã duyệt',
            self::STATUS_REJECTED => 'Từ chối',
            default => 'Không xác định',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'bg-warning text-dark',
            self::STATUS_APPROVED => 'bg-success',
            self::STATUS_REJECTED => 'bg-danger',
            default => 'bg-secondary',
        };
    }
}
