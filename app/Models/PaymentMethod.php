<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PaymentMethod extends Model
{
    use HasFactory;

    // Định nghĩa các hằng số cho trạng thái để code dễ đọc và bảo trì
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'description',
        'logo_path',
        'status',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['logo_full_url', 'status_text', 'status_badge_class'];


    /**
     * Lấy danh sách các trạng thái hợp lệ.
     *
     * @return array
     */
    public static function getAvailableStatus(): array
    {
        return [
            self::STATUS_ACTIVE,
            self::STATUS_INACTIVE,
        ];
    }

    /**
     * Accessor để lấy URL đầy đủ của logo.
     *
     * @return string
     */
    public function getLogoFullUrlAttribute(): string
    {
        // $this->logo_path sẽ chứa đường dẫn như 'payment_methods/abc.jpg'
        // Kiểm tra sự tồn tại của file trong disk 'public'
        if ($this->logo_path && Storage::disk('public')->exists($this->logo_path)) {
            // Sử dụng Storage::url() để tạo URL công khai cho file
            return Storage::url($this->logo_path);
        }
        return 'https://placehold.co/100x50/EFEFEF/AAAAAA&text=N/A';
    }

    /**
     * Kiểm tra xem phương thức có đang hoạt động không.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Accessor để lấy diễn giải của trạng thái.
     *
     * @return string
     */
    public function getStatusTextAttribute(): string
    {
        return $this->status === self::STATUS_ACTIVE ? 'Hoạt động' : 'Đã ẩn';
    }

    /**
     * Accessor để lấy lớp CSS cho badge trạng thái.
     *
     * @return string
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return $this->status === self::STATUS_ACTIVE ? 'bg-success' : 'bg-secondary';
    }


    /**
     * Quan hệ: Một phương thức thanh toán có thể được sử dụng trong nhiều đơn hàng.
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Quan hệ: Một phương thức thanh toán có thể được nhiều khách hàng lưu lại.
     */
    public function savedByCustomers()
    {
        return $this->belongsToMany(Customer::class, 'customer_saved_payment_methods');
    }
}
