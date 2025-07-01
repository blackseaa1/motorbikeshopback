<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder; // Import Builder

class VehicleModel extends Model
{
    use HasFactory;

    protected $table = 'vehicle_models';

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    // Hằng số cho các tùy chọn lọc/sắp xếp (NEW)
    const FILTER_STATUS_ALL = 'all';
    const FILTER_STATUS_ACTIVE_ONLY = 'active_only';
    const FILTER_STATUS_INACTIVE_ONLY = 'inactive_only';
    const FILTER_BY_BRAND = 'by_brand'; // Lọc theo hãng xe

    const SORT_BY_LATEST = 'latest';
    const SORT_BY_OLDEST = 'oldest';
    const SORT_BY_NAME_ASC = 'name_asc';
    const SORT_BY_NAME_DESC = 'name_desc';
    const SORT_BY_YEAR_ASC = 'year_asc';
    const SORT_BY_YEAR_DESC = 'year_desc';
    const SORT_BY_BRAND_NAME_ASC = 'brand_name_asc'; // Sắp xếp theo tên hãng xe


    protected $fillable = [
        'name',
        'vehicle_brand_id',
        'year',
        'description',
        'status',
    ];

    protected $appends = [
        'status_text',
        'status_badge_class'
    ];

    //======================================================================
    // RELATIONSHIPS
    //======================================================================

    public function vehicleBrand()
    {
        return $this->belongsTo(VehicleBrand::class, 'vehicle_brand_id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_vehicle_models', 'vehicle_model_id', 'product_id')
            ->withTimestamps();
    }

    //======================================================================
    // ACCESSORS
    //======================================================================

    public function getStatusTextAttribute(): string
    {
        return $this->isActive() ? 'Hoạt động' : 'Đã ẩn';
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return $this->isActive() ? 'bg-success' : 'bg-secondary';
    }

    //======================================================================
    // SCOPES & HELPERS (NEW)
    //======================================================================

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Scope để chỉ lấy các dòng xe đang hoạt động.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope để chỉ lấy các dòng xe đang ẩn.
     */
    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_INACTIVE);
    }

    /**
     * Scope để lọc theo hãng xe.
     * @param Builder $query
     * @param int $brandId
     * @return Builder
     */
    public function scopeByBrand(Builder $query, int $brandId): Builder
    {
        return $query->where('vehicle_brand_id', $brandId);
    }
}