<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder; // Import Builder

class Brand extends Model
{
    use HasFactory;

    protected $table = 'brands';

    // --- CONSTANTS ---
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

    // --- ATTRIBUTES ---
    protected $fillable = [
        'name',
        'description',
        'logo_url',
        'status',
    ];

    /**
     * Các thuộc tính ảo sẽ được thêm vào khi model được chuyển đổi thành array/JSON.
     */
    protected $appends = [
        'logo_full_url',
        'status_text',
        'status_badge_class'
    ];

    //======================================================================
    // RELATIONSHIPS
    //======================================================================

    /**
     * Lấy tất cả sản phẩm thuộc về thương hiệu này.
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'brand_id');
    }

    //======================================================================
    // ACCESSORS
    //======================================================================

    /**
     * Lấy URL đầy đủ của logo, trả về ảnh mặc định nếu không có.
     *
     * @return string
     */
    public function getLogoFullUrlAttribute(): string
    {
        return $this->logo_url ? asset('storage/' . $this->logo_url) : asset('assets_admin/img/default-logo.png');
    }

    /**
     * Lấy văn bản mô tả trạng thái (ví dụ: 'Hoạt động', 'Đã ẩn').
     *
     * @return string
     */
    public function getStatusTextAttribute(): string
    {
        return $this->isActive() ? 'Hoạt động' : 'Đã ẩn';
    }

    /**
     * Lấy lớp CSS cho badge trạng thái (ví dụ: 'bg-success', 'bg-secondary').
     *
     * @return string
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return $this->isActive() ? 'bg-success' : 'bg-secondary';
    }

    //======================================================================
    // SCOPES & HELPERS
    //======================================================================

    /**
     * Kiểm tra xem thương hiệu có đang hoạt động không.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Scope để chỉ lấy các thương hiệu đang hoạt động.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope để chỉ lấy các thương hiệu đang ẩn.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_INACTIVE);
    }
}
