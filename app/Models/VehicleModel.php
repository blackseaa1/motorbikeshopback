<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleModel extends Model
{
    use HasFactory;

    protected $table = 'vehicle_models';

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'name',
        'vehicle_brand_id',
        'year',
        'description',
        'status',
    ];

    /**
     * SỬA ĐỔI 1: Thêm $appends để tự động thêm accessor vào JSON.
     * @var array
     */
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

    /**
     * SỬA ĐỔI 2: Thêm các Accessor cho trạng thái.
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
