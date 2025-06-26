<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'status', // Đã cập nhật từ is_active sang status
    ];

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
