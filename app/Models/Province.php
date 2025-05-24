<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    use HasFactory;

    protected $table = 'provinces'; // [cite: 42]

    protected $fillable = [
        'name', // [cite: 43]
        'gso_id', // [cite: 43]
    ];

    /**
     * Các quận/huyện thuộc tỉnh/thành này.
     */
    public function districts()
    {
        return $this->hasMany(District::class, 'province_id'); // [cite: 45]
    }

    /**
     * Các đơn hàng có địa chỉ giao hàng ở tỉnh/thành này.
     */
    public function orders()
    {
        return $this->hasMany(Order::class, 'province_id'); // [cite: 67]
    }
}