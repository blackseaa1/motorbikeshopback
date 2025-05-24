<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryService extends Model
{
    use HasFactory;

    protected $table = 'delivery_services'; // [cite: 58]

    protected $fillable = [
        'name', // [cite: 59]
        'logo_url', // [cite: 59]
        'shipping_fee', // [cite: 59]
    ];

    protected $casts = [
        'shipping_fee' => 'decimal:2', // [cite: 59]
    ];

    /**
     * Các đơn hàng sử dụng dịch vụ giao hàng này.
     */
    public function orders()
    {
        return $this->hasMany(Order::class, 'delivery_service_id'); // [cite: 67]
    }
}