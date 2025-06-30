<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable; // Import Authenticatable
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany; // Import HasMany
use Illuminate\Database\Eloquent\Relations\HasOne; // Đảm bảo HasOne cũng được import nếu chưa có
use Illuminate\Database\Eloquent\SoftDeletes; // Đảm bảo SoftDeletes cũng được import nếu chưa có
use Illuminate\Support\Facades\Storage; // Đảm bảo Storage cũng được import nếu chưa có
use Illuminate\Database\Eloquent\Relations\MorphMany; // Đảm bảo MorphMany cũng được import nếu chưa có

class Customer extends Authenticatable // Kế thừa Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes; // Đảm bảo SoftDeletes được sử dụng nếu bạn muốn nó

    protected $table = 'customers';
    const STATUS_ACTIVE = 'active'; // Giữ các hằng số trạng thái nếu bạn có
    const STATUS_SUSPENDED = 'suspended'; // Giữ các hằng số trạng thái nếu bạn có

    /**
     * The attributes that are mass assignable.
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
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'phone_verified_at' => 'datetime',
        'password_change_required' => 'boolean',
    ];

    // Accessors (Nếu bạn có các accessors này, hãy giữ chúng)
    protected $appends = [
        'avatar_url', // Nếu bạn có accessor này
        'status_text', // Nếu bạn có accessor này
        'status_badge_class', // Nếu bạn có accessor này
        'is_active', // Nếu bạn có accessor này
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

    /**
     * Một khách hàng có nhiều địa chỉ.
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(CustomerAddress::class, 'customer_id')->orderByDesc('is_default');
    }

    /**
     * Một khách hàng có thể có nhiều phương thức thanh toán đã lưu.
     */
    public function savedPaymentMethods(): HasMany
    {
        return $this->hasMany(CustomerSavedPaymentMethod::class, 'customer_id');
    }

    /**
     * Một khách hàng có thể có một giỏ hàng.
     */
    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class, 'customer_id');
    }

    /**
     * SỬA ĐỔI: Một khách hàng có thể có nhiều đơn hàng.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    /**
     * Mối quan hệ với Review (Nếu bạn có model Review)
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'customer_id');
    }

    /**
     * Mối quan hệ đa hình với BlogPost (Nếu bạn có cấu trúc này)
     */
    public function blogPosts(): MorphMany
    {
        return $this->morphMany(BlogPost::class, 'author');
    }
}
