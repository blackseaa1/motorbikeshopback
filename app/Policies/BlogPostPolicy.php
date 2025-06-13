<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\BlogPost;
use Illuminate\Auth\Access\HandlesAuthorization;

class BlogPostPolicy
{
    use HandlesAuthorization;

    /**
     * Cho phép Super Admin thực hiện mọi hành động mà không cần kiểm tra thêm.
     * Đây là quyền lực tối cao của Super Admin.
     */
    public function before(Admin $admin, string $ability)
    {
        if ($admin->isSuperAdmin()) {
            return true;
        }
    }

    /**
     * Ai có thể xem danh sách bài viết?
     * (Tất cả các vai trò đã đăng nhập đều có thể vào trang danh sách)
     */
    public function viewAny(Admin $admin): bool
    {
        return true;
    }

    /**
     * Ai có thể xem chi tiết MỘT bài viết?
     * Super Admin đã được cho phép ở hàm `before`.
     */
    public function view(Admin $admin, BlogPost $blogPost): bool
    {
        // Các vai trò khác (kể cả Admin) chỉ có thể xem:
        // a) Bài viết của chính họ (bất kể trạng thái).
        // b) Hoặc bất kỳ bài viết nào đã được xuất bản của người khác.
        return $admin->id === $blogPost->author_id || $blogPost->isPublished();
    }

    /**
     * Ai có thể tạo bài viết mới?
     */
    public function create(Admin $admin): bool
    {
        return true;
    }

    /**
     * Ai có thể cập nhật một bài viết?
     * Super Admin đã được cho phép ở hàm `before`.
     */
    public function update(Admin $admin, BlogPost $blogPost): bool
    {
        // Các vai trò khác chỉ có thể cập nhật bài viết của chính họ.
        return $admin->id === $blogPost->author_id;
    }

    /**
     * Ai có thể xóa (xóa mềm) một bài viết?
     * Super Admin đã được cho phép ở hàm `before`.
     */
    public function delete(Admin $admin, BlogPost $blogPost): bool
    {
        // Các vai trò khác chỉ có thể xóa bài viết của chính họ.
        return $admin->id === $blogPost->author_id;
    }

    /**
     * Ai có thể khôi phục một bài viết?
     * Super Admin đã được cho phép ở hàm `before`.
     */
    public function restore(Admin $admin, BlogPost $blogPost): bool
    {
        // Các vai trò khác chỉ có thể khôi phục bài viết của chính họ.
        return $admin->id === $blogPost->author_id;
    }

    /**
     * Ai có thể xóa vĩnh viễn một bài viết?
     * Chỉ Super Admin mới có quyền này (đã xử lý ở `before`).
     * Các vai trò khác sẽ bị từ chối.
     */
    public function forceDelete(Admin $admin, BlogPost $blogPost): bool
    {
        return false;
    }

    /**
     * Ai có quyền Xuất bản hoặc thay đổi trạng thái?
     * Chỉ Super Admin và Admin mới có quyền này.
     * Super Admin đã được xử lý ở `before`.
     */
    public function toggleStatus(Admin $admin, ?BlogPost $blogPost = null): bool
    {
        return $admin->role === Admin::ROLE_ADMIN;
    }
}
