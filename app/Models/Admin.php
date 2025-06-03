<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'admins';

    // Định nghĩa các hằng số trạng thái bằng tiếng Anh
    const STATUS_ACTIVE = 'active';
    const STATUS_SUSPENDED = 'suspended';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'role',
        'password',
        'img',
        'status', // Đảm bảo đã có trong $fillable
    ];

    protected $hidden = [
        'password',
    ];

    /**
     * Các đơn hàng được tạo bởi admin này.
     */
    public function createdOrders()
    {
        return $this->hasMany(Order::class, 'created_by_admin_id');
    }
}
