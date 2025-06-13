<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // THÊM MỚI: Import SoftDeletes
use Illuminate\Support\Facades\Storage;

class BlogPost extends Model
{
    // THÊM MỚI: Sử dụng SoftDeletes trait
    use HasFactory, SoftDeletes;

    protected $table = 'blog_posts';

    // Hằng số trạng thái để dễ quản lý
    const STATUS_PUBLISHED = 'published';
    const STATUS_DRAFT = 'draft';
    const STATUS_ARCHIVED = 'archived';
    const STATUS_PENDING = 'pending'; // Thêm trạng thái chờ duyệt

    /**
     * Các trường được phép gán hàng loạt.
     * 'author_type' rất quan trọng cho quan hệ đa hình.
     */
    protected $fillable = [
        'title',
        'content',
        'image_url',
        'author_id',
        'author_type',
        'status',
    ];

    /**
     * Các accessor sẽ được tự động thêm vào khi model chuyển thành array/JSON.
     */
    protected $appends = ['image_full_url', 'status_info'];


    /**
     * Quan hệ đa hình `author`
     * Cho phép một bài viết có thể thuộc về Admin hoặc một Model khác (ví dụ: Customer).
     */
    public function author()
    {
        return $this->morphTo();
    }

    /**
     * Accessor: Lấy URL đầy đủ của ảnh đại diện.
     * @return string
     */
    public function getImageFullUrlAttribute()
    {
        $imagePath = $this->attributes['image_url'];
        if ($imagePath && Storage::disk('public')->exists($imagePath)) {
            return Storage::url($imagePath);
        }
        return 'https://placehold.co/400x250/EFEFEF/AAAAAA&text=No+Image';
    }

    /**
     * Accessor: Lấy thông tin hiển thị chi tiết của trạng thái (text và màu badge).
     * @return array
     */
    public function getStatusInfoAttribute()
    {
        // THÊM MỚI: Xử lý trạng thái trong thùng rác
        if ($this->trashed()) {
            return ['text' => 'Trong thùng rác', 'badge' => 'bg-danger'];
        }

        switch ($this->status) {
            case self::STATUS_PUBLISHED:
                return ['text' => 'Đã xuất bản', 'badge' => 'bg-success'];
            case self::STATUS_DRAFT:
                return ['text' => 'Bản nháp', 'badge' => 'bg-secondary'];
            case self::STATUS_PENDING:
                return ['text' => 'Chờ duyệt', 'badge' => 'bg-info'];
            case self::STATUS_ARCHIVED:
                return ['text' => 'Ẩn bài viết', 'badge' => 'bg-warning text-dark'];
            default:
                return ['text' => 'Không rõ', 'badge' => 'bg-light text-dark'];
        }
    }

    /**
     * Helper kiểm tra trạng thái
     */
    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }
}
