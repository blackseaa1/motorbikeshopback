<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryService extends Model
{
    use HasFactory;

    protected $table = 'delivery_services';

    // Định nghĩa các hằng số cho trạng thái
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'name',
        'logo_url',
        'shipping_fee',
        'status', // Đảm bảo cột này có trong database và migration
    ];

    protected $casts = [
        'shipping_fee' => 'decimal:0', // Hiển thị số nguyên cho VNĐ
    ];

    /**
     * Các đơn hàng sử dụng dịch vụ giao hàng này.
     */
    public function orders()
    {
        return $this->hasMany(Order::class, 'delivery_service_id');
    }

    /**
     * Kiểm tra xem dịch vụ có đang hoạt động không.
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->status === self::STATUS_ACTIVE;
    }
}
