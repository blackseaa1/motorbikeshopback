<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $table = 'categories'; //

    // Định nghĩa các hằng số cho trạng thái (sử dụng tiếng Anh cho giá trị trong DB)
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'name', //
        'description', //
        'status', // Đảm bảo đã có cột này trong CSDL và migration
    ];

    /**
     * Các sản phẩm thuộc danh mục này.
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'category_id'); //
    }

    /**
     * Scope để chỉ lấy các danh mục đang hoạt động.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Kiểm tra xem danh mục có đang hoạt động không.
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->status === self::STATUS_ACTIVE;
    }
}
