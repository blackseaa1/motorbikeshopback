<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;

    protected $table = 'brands';

    protected $fillable = [
        'name',
        'description',
        'logo_url',
        'status',
    ];

    /**
     * Các sản phẩm thuộc thương hiệu này.
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'brand_id');
    }
}
