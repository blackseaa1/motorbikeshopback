<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use HasFactory;

    protected $table = 'promotions';

    protected $fillable = [
        'code',
        'discount_percentage',
        'start_date',
        'end_date',
        'usage_count',
        'status',
    ];

    protected $casts = [
        'discount_percentage' => 'decimal:2',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'usage_count' => 'integer',
    ];

    /**
     * Các đơn hàng sử dụng mã khuyến mãi này.
     */
    public function orders()
    {
        return $this->hasMany(Order::class, 'promotion_id');
    }
}
