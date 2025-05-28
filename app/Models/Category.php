<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $table = 'categories'; // [cite: 48]

    protected $fillable = [
        'name', // [cite: 49]
        'description', // [cite: 49]
    ];

    /**
     * Các sản phẩm thuộc danh mục này.
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'category_id'); // [cite: 61]
    }
}
