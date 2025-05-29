<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductVehicleModel extends Pivot
{
    use HasFactory;

    protected $table = 'product_vehicle_models';
    public $incrementing = false; 

    protected $fillable = [
        'product_id',
        'vehicle_model_id',
    ];

    // Nếu bạn muốn truy cập model Product hoặc VehicleModel từ model pivot này (ít dùng)
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function vehicleModel()
    {
        return $this->belongsTo(VehicleModel::class, 'vehicle_model_id');
    }
}