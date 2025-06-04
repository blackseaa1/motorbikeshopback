<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleModel extends Model
{
    use HasFactory;

    protected $table = 'vehicle_models'; // Đảm bảo tên bảng đúng

    // Định nghĩa các hằng số cho trạng thái
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
     * Hãng xe của dòng xe này.
     */
    public function vehicleBrand()
    {
        return $this->belongsTo(VehicleBrand::class, 'vehicle_brand_id');
    }

    /**
     * Các sản phẩm thuộc dòng xe này. (Thêm nếu bạn có model Product)
     */
    public function products()
    {
        // return $this->hasMany(Product::class, 'vehicle_model_id');
        // Giả sử bạn có một model Product và cột vehicle_model_id
        // Nếu không, bạn có thể comment hoặc xóa dòng này
        // Trong VehicleModelController, bạn có kiểm tra $vehicleModel->products()->exists()
        // nên bạn cần định nghĩa relation này hoặc thay đổi logic kiểm tra đó.
        // Tạm thời, tôi sẽ comment nó đi để tránh lỗi nếu bạn chưa có Product model.
        return $this->hasMany('App\Models\Product'); // Thay 'App\Models\Product' bằng model Product thực tế
    }


    /**
     * Kiểm tra xem dòng xe có đang hoạt động không.
     */
    public function isActive()
    {
        return $this->status === self::STATUS_ACTIVE;
    }
}
