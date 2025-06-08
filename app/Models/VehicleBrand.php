<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage; // Thêm use Storage

class VehicleBrand extends Model
{
    use HasFactory;

    protected $table = 'vehicle_brands';

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'name',
        'description',
        'logo_url',
        'status',
    ];

    /**
     * SỬA ĐỔI 1: Thêm $appends để tự động thêm accessor vào JSON.
     * @var array
     */
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

    /**
     * SỬA ĐỔI 2: Accessor để lấy URL đầy đủ của logo.
     */
    public function getLogoFullUrlAttribute()
    {
        $logoPath = $this->attributes['logo_url'];
        if ($logoPath && Storage::disk('public')->exists($logoPath)) {
            return Storage::url($logoPath);
        }
        return 'https://placehold.co/100x100/EFEFEF/AAAAAA&text=LOGO';
    }

    /**
     * SỬA ĐỔI 3: Thêm các Accessor cho trạng thái.
     */
    public function getStatusTextAttribute(): string
    {
        return $this->isActive() ? 'Hoạt động' : 'Đã ẩn';
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return $this->isActive() ? 'bg-success' : 'bg-secondary';
    }

    //======================================================================
    // SCOPES & HELPERS
    //======================================================================

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function isActive()
    {
        return $this->status === self::STATUS_ACTIVE;
    }
}
