<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model; // Hoặc Relations\Pivot

class Review extends Model // Hoặc Pivot
{
    use HasFactory;

    protected $table = 'reviews'; 
    // protected $primaryKey = ['customer_id', 'product_id']; // Laravel không hỗ trợ array primary key

    public $incrementing = false; // Không có cột id auto-increment đơn lẻ


    protected $fillable = [
        'customer_id',
        'product_id',
        'rating',
        'comment',
        'status',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    /**
     * Khách hàng đã viết đánh giá.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Sản phẩm được đánh giá.
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
