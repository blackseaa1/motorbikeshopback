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
use Illuminate\Database\Eloquent\Relations\MorphMany; // SỬA ĐỔI 1: Thêm use MorphMany

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

    protected $table = 'customers';
    const STATUS_ACTIVE = 'active';
    const STATUS_SUSPENDED = 'suspended';

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'img',
        'status',
        'password_change_required',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'password_change_required' => 'boolean',
    ];

    protected $appends = [
        'avatar_url',
        'status_text',
        'status_badge_class',
        'is_active',
    ];

    public function getAvatarUrlAttribute(): string
    {
        if ($this->img && Storage::disk('public')->exists($this->img)) {
            return Storage::url($this->img);
        }
        return 'https://placehold.co/100x100/EFEFEF/AAAAAA&text=KH';
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->isActive();
    }

    public function getStatusTextAttribute(): string
    {
        return $this->isActive() ? 'Hoạt động' : 'Bị khóa';
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return $this->isActive() ? 'bg-success' : 'bg-danger';
    }

    /*
    |--------------------------------------------------------------------------
    | Mối quan hệ (Relationships)
    |--------------------------------------------------------------------------
    */
    public function addresses(): HasMany
    {
        return $this->hasMany(CustomerAddress::class)->orderByDesc('is_default');
    }

    /**
     * Lấy địa chỉ mặc định của khách hàng.
     */
    public function defaultAddress(): HasOne
    {
        return $this->hasOne(CustomerAddress::class)->where('is_default', true);
    }
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function reviews(): HasMany
    {
        // Giả sử bạn sẽ có model Review
        // return $this->hasMany(Review::class);
        return $this->hasMany('App\Models\Review');
    }

    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class);
    }

    /**
     * SỬA ĐỔI 2: Chuyển quan hệ sang đa hình (Polymorphic).
     * Một khách hàng có thể là tác giả của nhiều bài viết.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function blogPosts(): MorphMany
    {
        return $this->morphMany(BlogPost::class, 'author');
    }
}
