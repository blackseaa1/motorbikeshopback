<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleBrand extends Model
{
    use HasFactory;

    protected $table = 'vehicle_brands'; // Đảm bảo tên bảng đúng

    // Định nghĩa các hằng số cho trạng thái
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'name',
        'description',
        'logo_url',
        'status',
    ];

    /**
     * Các dòng xe thuộc hãng này.
     */
    public function vehicleModels()
    {
        return $this->hasMany(VehicleModel::class, 'vehicle_brand_id');
    }

    /**
     * Kiểm tra xem hãng xe có đang hoạt động không.
     */
    public function isActive()
    {
        return $this->status === self::STATUS_ACTIVE;
    }
}
