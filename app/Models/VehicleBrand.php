<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleBrand extends Model
{
    use HasFactory;

    protected $table = 'vehicle_brands'; 

    protected $fillable = [
        'name', 
        'description', 
        'logo_url', 
        'status',
    ];

    /**
     * Các mẫu xe thuộc hãng xe này.
     */
    public function vehicleModels()
    {
        return $this->hasMany(VehicleModel::class, 'vehicle_brand_id');
    }
}
