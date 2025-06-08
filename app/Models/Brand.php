<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage; // Thêm use Storage

class Brand extends Model
{
    use HasFactory;

    protected $table = 'brands';

    // Định nghĩa các hằng số cho trạng thái
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'name',
        'description',
        'logo_url',
        'status',
    ];

    /**
     * SỬA ĐỔI 1: Thêm mảng $appends.
     * Các accessor trong mảng này sẽ được tự động thêm vào khi model
     * được chuyển đổi thành array hoặc JSON.
     * @var array
     */
    protected $appends = ['logo_full_url'];

    /**
     * SỬA ĐỔI 2: Thêm Accessor để lấy URL đầy đủ của logo.
     * Giúp tập trung logic xử lý URL vào Model.
     *
     * @return string
     */
    public function getLogoFullUrlAttribute()
    {
        // Lấy giá trị gốc từ DB, tránh vòng lặp vô hạn
        $logoPath = $this->attributes['logo_url'];

        if ($logoPath && Storage::disk('public')->exists($logoPath)) {
            return Storage::url($logoPath);
        }
        // Trả về ảnh mặc định nếu không có logo
        return 'https://placehold.co/100x100/EFEFEF/AAAAAA&text=LOGO';
    }


    /**
     * Các sản phẩm thuộc thương hiệu này.
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'brand_id');
    }

    /**
     * Scope để chỉ lấy các thương hiệu đang hoạt động.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Kiểm tra xem thương hiệu có đang hoạt động không.
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->status === self::STATUS_ACTIVE;
    }
}
