<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductVehicleModel extends Pivot
{
    use HasFactory;

    protected $table = 'product_vehicle_models'; // [cite: 64]
    public $incrementing = true; // Vì chúng ta dùng primary key phức hợp

    // Không cần $fillable nếu bạn chỉ tạo/cập nhật qua attach/sync/detach của belongsToMany
    // Nhưng nếu bạn tạo record trực tiếp vào bảng này thì cần
    protected $fillable = [
        'product_id', // [cite: 65]
        'vehicle_model_id', // [cite: 65]
    ];

    // Nếu bạn muốn truy cập model Product hoặc VehicleModel từ model pivot này (ít dùng)
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id'); // [cite: 65]
    }

    public function vehicleModel()
    {
        return $this->belongsTo(VehicleModel::class, 'vehicle_model_id'); // [cite: 65]
    }
}