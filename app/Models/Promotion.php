<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use HasFactory;

    protected $table = 'promotions'; // [cite: 56]

    protected $fillable = [
        'code', // [cite: 57]
        'discount_percentage', // [cite: 57]
        'start_date', // [cite: 57]
        'end_date', // [cite: 57]
        'usage_count', // [cite: 57]
    ];

    protected $casts = [
        'discount_percentage' => 'decimal:2', // [cite: 57]
        'start_date' => 'datetime', // [cite: 57]
        'end_date' => 'datetime', // [cite: 57]
        'usage_count' => 'integer', // [cite: 57]
    ];

    /**
     * Các đơn hàng sử dụng mã khuyến mãi này.
     */
    public function orders()
    {
        return $this->hasMany(Order::class, 'promotion_id'); // [cite: 67]
    }
}