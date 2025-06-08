<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlogPost extends Model
{
    use HasFactory;

    protected $table = 'blog_posts';

    /**
     * SỬA ĐỔI 1: Thêm 'author_type' vào $fillable
     * Cần thêm 'author_type' để có thể gán hàng loạt khi tạo bài viết
     * cho quan hệ đa hình.
     */
    protected $fillable = [
        'title',
        'content',
        'image_url',
        'author_id',
        'author_type', // Thêm dòng này
        'status',
    ];

    /**
     * SỬA ĐỔI 2: Thay đổi quan hệ 'author' thành đa hình (Polymorphic)
     * Phương thức `morphTo()` cho phép BlogPost này thuộc về nhiều loại Model khác nhau
     * (trong trường hợp này là Admin hoặc Customer).
     * Laravel sẽ tự động sử dụng các cột 'author_id' và 'author_type' để tìm ra tác giả chính xác.
     */
    public function author()
    {
        return $this->morphTo();
    }
}
