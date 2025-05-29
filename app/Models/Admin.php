<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable; // Để có thể sử dụng cho Auth nếu cần
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'admin';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'role',
        'password',
        'img', 
        'status',
    ];

    protected $hidden = [
        'password',
    ];

    /**
     * Các đơn hàng được tạo bởi admin này.
     */
    public function createdOrders()
    {
        return $this->hasMany(Order::class, 'created_by_admin_id'); // [cite: 104]
    }
}
