<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder; // Import Builder

class Category extends Model
{
    use HasFactory;

    protected $table = 'categories';

    // Định nghĩa các hằng số cho trạng thái
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    // Hằng số cho các tùy chọn lọc/sắp xếp
    const FILTER_STATUS_ALL = 'all';
    const FILTER_STATUS_ACTIVE_ONLY = 'active_only';
    const FILTER_STATUS_INACTIVE_ONLY = 'inactive_only';

    const SORT_BY_LATEST = 'latest';
    const SORT_BY_OLDEST = 'oldest';
    const SORT_BY_NAME_ASC = 'name_asc';
    const SORT_BY_NAME_DESC = 'name_desc';


    protected $fillable = [
        'name',
        'description',
        'status',
    ];

    /**
     * Thêm các accessor vào các dạng biểu diễn mảng/JSON của model.
     * @var array
     */
    protected $appends = [
        'status_text',
        'status_badge_class'
    ];


    //======================================================================
    // RELATIONSHIPS (CÁC MỐI QUAN HỆ)
    //======================================================================

    /**
     * Các sản phẩm thuộc danh mục này.
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    //======================================================================
    // ACCESSORS (TRÌNH TRUY CẬP)
    //======================================================================

    /**
     * Lấy văn bản mô tả trạng thái (ví dụ: 'Hoạt động', 'Đã ẩn').
     */
    public function getStatusTextAttribute(): string
    {
        return $this->isActive() ? 'Hoạt động' : 'Đã ẩn';
    }

    /**
     * Lấy lớp CSS cho badge trạng thái (ví dụ: 'bg-success', 'bg-secondary').
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return $this->isActive() ? 'bg-success' : 'bg-secondary';
    }

    //======================================================================
    // SCOPES & HELPERS (PHẠM VI & HÀM HỖ TRỢ)
    //======================================================================

    /**
     * Scope để chỉ lấy các danh mục đang hoạt động.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope để chỉ lấy các danh mục đang ẩn.
     */
    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_INACTIVE);
    }

    /**
     * Kiểm tra xem danh mục có đang hoạt động không.
     * Phương thức này đã được cập nhật để chấp nhận cả giá trị 'visible' cũ là trạng thái hoạt động.
     */
    public function isActive(): bool
    {
        // Kiểm tra xem status có nằm trong mảng các giá trị được coi là "active" không.
        return in_array($this->status, [self::STATUS_ACTIVE, 'visible']);
    }
}