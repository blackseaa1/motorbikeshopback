<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlogPost extends Model
{
    use HasFactory;

    protected $table = 'blog_posts'; // [cite: 76]

    protected $fillable = [
        'title', // [cite: 77]
        'content', // [cite: 77]
        'image_url', // [cite: 77]
        'author_id', // [cite: 77]
    ];

    /**
     * Tác giả (khách hàng) của bài viết blog.
     * Nếu admin cũng có thể viết, bạn cần một bảng users chung hoặc polymorphic relationship.
     * Theo schema hiện tại, author_id là FK đến customers(id).
     */
    public function author()
    {
        // Giả sử tác giả luôn là Customer dựa trên FK 'author_id' REFERENCES 'customers(id)'
        return $this->belongsTo(Customer::class, 'author_id'); // [cite: 77, 110]
    }
}