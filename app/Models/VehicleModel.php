<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleModel extends Model
{
    use HasFactory;

    protected $table = 'vehicle_models';

    protected $fillable = [
        'vehicle_brand_id',
        'name',
        'year',
        'description',
        'status',
    ];

    protected $casts = [
        'year' => 'integer',
    ];

    /**
     * Hãng xe mà mẫu xe này thuộc về.
     */
    public function vehicleBrand()
    {
        return $this->belongsTo(VehicleBrand::class, 'vehicle_brand_id');
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
