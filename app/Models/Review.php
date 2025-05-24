<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model; // Hoặc Relations\Pivot

class Review extends Model // Hoặc Pivot
{
    use HasFactory;

    protected $table = 'reviews'; // [cite: 70]
    // protected $primaryKey = ['customer_id', 'product_id']; // Laravel không hỗ trợ array primary key

    public $incrementing = true; // Không có cột id auto-increment đơn lẻ


    protected $fillable = [
        'customer_id', // [cite: 71]
        'product_id', // [cite: 71]
        'rating', // [cite: 71]
        'comment', // [cite: 71]
    ];

    protected $casts = [
        'rating' => 'integer', // [cite: 71]
    ];

    /**
     * Khách hàng đã viết đánh giá.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id'); // [cite: 71]
    }

    /**
     * Sản phẩm được đánh giá.
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id'); // [cite: 71]
    }
}