<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryService extends Model
{
    use HasFactory;

    protected $table = 'delivery_services';

    protected $fillable = [
        'name',
        'logo_url',
        'shipping_fee',
        'status',
    ];

    protected $casts = [
        'shipping_fee' => 'decimal:2',
    ];

    /**
     * Các đơn hàng sử dụng dịch vụ giao hàng này.
     */
    public function orders()
    {
        return $this->hasMany(Order::class, 'delivery_service_id'); // [cite: 67]
    }
}
