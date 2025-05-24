<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;

    protected $table = 'brands'; // [cite: 50]

    protected $fillable = [
        'name', // [cite: 51]
        'description', // [cite: 51]
        'logo_url', // [cite: 51]
    ];

    /**
     * Các sản phẩm thuộc thương hiệu này.
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'brand_id'); // [cite: 61]
    }
}