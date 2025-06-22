<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage; // Thêm use Storage

class ProductImage extends Model
{
    use HasFactory;

    protected $table = 'product_images';

    /**
     * SỬA ĐỔI 1: Sửa lại tên cột trong fillable từ image_path thành image_url để khớp migration
     * và thêm alt_text nếu bạn muốn lưu nó.
     */
    protected $fillable = [
        'product_id',
        'image_url', // Đổi từ image_path để khớp với migration 2025_05_21_210203
        // 'alt_text', // Bỏ comment nếu bạn có cột này
    ];

    /**
     * SỬA ĐỔI 2: Thêm mảng $appends.
     * @var array
     */
    protected $appends = ['image_full_url'];
    public function getImageFullUrlAttribute(): string
    {
        $path = $this->image_url; // Lấy đường dẫn lưu trong CSDL

        // Nếu đường dẫn tồn tại và file có thật trong storage
        if ($path && Storage::disk('public')->exists($path)) {
            return Storage::url($path); // Trả về URL đầy đủ
        }

        // Nếu không, trả về ảnh mặc định
        return asset('https://placehold.co/400x400/EFEFEF/AAAAAA&text=Image');
    }
    /**
     * Sản phẩm mà hình ảnh này thuộc về.
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * SỬA ĐỔI 3: Thêm Accessor để lấy URL đầy đủ của ảnh.
     */
    // public function getImageFullUrlAttribute(): string
    // {
    //     $path = $this->image_url; // Dùng thuộc tính image_url
    //     if ($path && Storage::disk('public')->exists($path)) {
    //         return Storage::url($path);
    //     }
    //     // Trả về ảnh placeholder cho sản phẩm
    //     return 'https://placehold.co/400x400/EFEFEF/AAAAAA&text=Image';
    // }
}
