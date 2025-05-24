<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    use HasFactory;

    protected $table = 'districts'; // [cite: 44]

    protected $fillable = [
        'name', // [cite: 45]
        'gso_id', // [cite: 45]
        'province_id', // [cite: 45]
    ];

    /**
     * Tỉnh/thành mà quận/huyện này thuộc về.
     */
    public function province()
    {
        return $this->belongsTo(Province::class, 'province_id'); // [cite: 45]
    }

    /**
     * Các phường/xã thuộc quận/huyện này.
     */
    public function wards()
    {
        return $this->hasMany(Ward::class, 'district_id'); // [cite: 47]
    }

    /**
     * Các đơn hàng có địa chỉ giao hàng ở quận/huyện này.
     */
    public function orders()
    {
        return $this->hasMany(Order::class, 'district_id'); // [cite: 67]
    }
}