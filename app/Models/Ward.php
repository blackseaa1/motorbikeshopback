<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ward extends Model
{
    use HasFactory;

    protected $table = 'wards'; // [cite: 46]

    protected $fillable = [
        'name', // [cite: 47]
        'gso_id', // [cite: 47]
        'district_id', // [cite: 47]
    ];

    /**
     * Quận/huyện mà phường/xã này thuộc về.
     */
    public function district()
    {
        return $this->belongsTo(District::class, 'district_id'); // [cite: 47]
    }

    /**
     * Các đơn hàng có địa chỉ giao hàng ở phường/xã này.
     */
    public function orders()
    {
        return $this->hasMany(Order::class, 'ward_id'); // [cite: 67]
    }
}