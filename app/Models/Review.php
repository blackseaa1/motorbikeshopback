<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $table = 'reviews';

    // Khai báo khóa chính phức hợp
    protected $primaryKey = ['customer_id', 'product_id'];
    // Tắt tính năng tự tăng ID vì khóa chính là phức hợp
    public $incrementing = false;
    // RẤT QUAN TRỌNG: Khai báo kiểu dữ liệu của khóa chính là 'array'
    protected $keyType = 'array';

    /**
     * Định nghĩa các hằng số cho trạng thái.
     */
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    /**
     * Hằng số STATUSES kiểu mảng.
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
     * Các thuộc tính được thêm vào khi serialize Model.
     * @var array
     */
    protected $appends = ['status_text', 'status_badge_class'];

    protected $casts = [
        'rating' => 'integer',
    ];

    /**
     * Ghi đè phương thức để đặt các khóa cho truy vấn lưu/cập nhật.
     * Điều này là cần thiết cho các khóa chính phức hợp để đảm bảo Laravel tìm đúng bản ghi để cập nhật.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function setKeysForSaveQuery($query)
    {
        // Thêm điều kiện where cho từng phần của khóa chính phức hợp.
        // Điều này đảm bảo khi phương thức save() được gọi, Laravel biết cách tìm bản ghi cụ thể.
        $query->where('customer_id', $this->getAttribute('customer_id'))
            ->where('product_id', $this->getAttribute('product_id'));

        return $query;
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Accessor cho trạng thái hiển thị bằng văn bản.
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

    /**
     * Accessor cho class badge CSS dựa trên trạng thái.
     */
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
