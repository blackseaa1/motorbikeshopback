<?php
// app/Models/Customer.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

/**
 * Class Customer
 *
 * Đại diện cho một tài khoản khách hàng trong hệ thống.
 * Kế thừa từ Authenticatable để có thể sử dụng các tính năng đăng nhập.
 * Sử dụng SoftDeletes để hỗ trợ chức năng "Thùng rác".
 *
 * @package App\Models
 */
class Customer extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * Tên bảng trong cơ sở dữ liệu.
     *
     * @var string
     */
    protected $table = 'customers';

    /**
     * Hằng số cho các trạng thái tài khoản.
     */
    const STATUS_ACTIVE = 'active';
    const STATUS_SUSPENDED = 'suspended';

    /**
     * Các trường được phép gán hàng loạt (mass assignable).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'img',
        'status',
        'password_change_required',
    ];

    /**
     * Các trường sẽ bị ẩn khi model được chuyển đổi thành array hoặc JSON.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Tự động chuyển đổi kiểu dữ liệu cho các thuộc tính.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed', // Tự động hash mật khẩu khi gán
        'password_change_required' => 'boolean',
    ];

    /**
     * Các accessor sẽ được tự động thêm vào khi model chuyển thành array/JSON.
     * Rất quan trọng để các thuộc tính ảo có thể được sử dụng ở phía client (JavaScript).
     *
     * @var array<int, string>
     */
    protected $appends = [
        'avatar_url',
        'status_text',
        'status_badge_class',
        'is_active',
    ];

    /**
     * Accessor: Lấy URL ảnh đại diện.
     * Trả về ảnh mặc định nếu không có ảnh hoặc file không tồn tại.
     *
     * Cách dùng: $customer->avatar_url
     *
     * @return string
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->img && Storage::disk('public')->exists($this->img)) {
            return Storage::url($this->img);
        }
        // Trả về ảnh placeholder mặc định
        return 'https://placehold.co/100x100/EFEFEF/AAAAAA&text=KH';
    }

    /**
     * Helper: Kiểm tra tài khoản có đang ở trạng thái hoạt động không.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Accessor: Lấy giá trị boolean cho trạng thái hoạt động.
     * Thuộc tính này được thêm vào để JavaScript có thể sử dụng.
     *
     * Cách dùng: $customer->is_active
     *
     * @return bool
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->isActive();
    }

    /**
     * Accessor: Lấy tên trạng thái để hiển thị cho người dùng.
     *
     * Cách dùng: $customer->status_text
     *
     * @return string
     */
    public function getStatusTextAttribute(): string
    {
        return $this->isActive() ? 'Hoạt động' : 'Bị khóa';
    }

    /**
     * Accessor: Lấy class CSS của Bootstrap cho badge trạng thái.
     *
     * Cách dùng: $customer->status_badge_class
     *
     * @return string
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return $this->isActive() ? 'bg-success' : 'bg-danger';
    }

    /*
    |--------------------------------------------------------------------------
    | Mối quan hệ (Relationships)
    |--------------------------------------------------------------------------
    */

    /**
     * Một khách hàng có thể có nhiều đơn hàng.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Một khách hàng có thể có nhiều đánh giá.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Một khách hàng có một giỏ hàng.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class);
    }

    /**
     * Một khách hàng có thể là tác giả của nhiều bài viết.
     * (Giả sử khách hàng cũng có thể viết blog)
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function blogPosts(): HasMany
    {
        return $this->hasMany(BlogPost::class, 'author_id');
    }
}
