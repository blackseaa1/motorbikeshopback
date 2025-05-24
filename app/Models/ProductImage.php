<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    use HasFactory;

    protected $table = 'product_images'; // [cite: 62]

    protected $fillable = [
        'product_id', // [cite: 63]
        'image_url', // [cite: 63]
    ];

    /**
     * Sản phẩm mà hình ảnh này thuộc về.
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id'); // [cite: 63]
    }
}