<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder; // Import Builder

class VehicleBrand extends Model
{
    use HasFactory;

    protected $table = 'vehicle_brands';

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    // Hằng số cho các tùy chọn lọc/sắp xếp (NEW)
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
        'logo_url',
        'status',
    ];

    protected $appends = [
        'logo_full_url',
        'status_text',
        'status_badge_class'
    ];


    //======================================================================
    // RELATIONSHIPS
    //======================================================================

    public function vehicleModels()
    {
        return $this->hasMany(VehicleModel::class, 'vehicle_brand_id');
    }

    //======================================================================
    // ACCESSORS
    //======================================================================

    public function getLogoFullUrlAttribute(): string
    {
        $logoPath = $this->attributes['logo_url'];
        if ($logoPath && Storage::disk('public')->exists($logoPath)) {
            return Storage::url($logoPath);
        }
        return asset('assets_admin/img/default-logo.png'); // Fallback default image
    }

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
     * Scope để chỉ lấy các thương hiệu đang hoạt động.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope để chỉ lấy các thương hiệu đang ẩn.
     */
    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_INACTIVE);
    }
}