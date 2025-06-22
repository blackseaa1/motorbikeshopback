<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ProductImage extends Model
{
    use HasFactory;

    protected $table = 'product_images';

    protected $fillable = [
        'product_id',
        'image_url',
        // 'alt_text',
    ];

    protected $appends = ['image_full_url'];

    /**
     * Sản phẩm mà hình ảnh này thuộc về.
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * ĐÃ SỬA: Accessor để lấy URL đầy đủ của ảnh.
     * Đảm bảo luôn trả về một chuỗi, kể cả khi ảnh không tồn tại.
     */
    public function getImageFullUrlAttribute(): string
    {
        $path = $this->image_url;
        if ($path && Storage::disk('public')->exists($path)) {
            return Storage::url($path);
        }
        // Trả về một URL ảnh mặc định nếu tệp không tồn tại hoặc đường dẫn là null/rỗng
        return 'https://placehold.co/400x400/EFEFEF/AAAAAA&text=No+Image';
    }
}
