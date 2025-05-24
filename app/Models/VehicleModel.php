<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleModel extends Model
{
    use HasFactory;

    protected $table = 'vehicle_models'; // [cite: 54]

    protected $fillable = [
        'vehicle_brand_id', // [cite: 55]
        'name', // [cite: 55]
        'year', // [cite: 55]
        'description', // [cite: 55]
    ];

    protected $casts = [
        'year' => 'integer', // [cite: 55]
    ];

    /**
     * Hãng xe mà mẫu xe này thuộc về.
     */
    public function vehicleBrand()
    {
        return $this->belongsTo(VehicleBrand::class, 'vehicle_brand_id'); // [cite: 55]
    }

    /**
     * Các sản phẩm tương thích với mẫu xe này.
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_vehicle_models', 'vehicle_model_id', 'product_id') // [cite: 65]
                    ->withTimestamps();
    }
}