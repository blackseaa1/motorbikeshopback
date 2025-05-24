<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleBrand extends Model
{
    use HasFactory;

    protected $table = 'vehicle_brands'; // [cite: 52]

    protected $fillable = [
        'name', // [cite: 53]
        'description', // [cite: 53]
        'logo_url', // [cite: 53]
    ];

    /**
     * Các mẫu xe thuộc hãng xe này.
     */
    public function vehicleModels()
    {
        return $this->hasMany(VehicleModel::class, 'vehicle_brand_id'); // [cite: 55]
    }
}