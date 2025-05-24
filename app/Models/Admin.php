<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable; // Để có thể sử dụng cho Auth nếu cần
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'admin'; // [cite: 38]

    protected $fillable = [
        'name', // [cite: 39]
        'email', // [cite: 39]
        'phone', // [cite: 39]
        'role', // [cite: 39]
        'password', // [cite: 39]
        'img', // [cite: 39]
    ];

    protected $hidden = [
        'password', // [cite: 39]
    ];

    /**
     * Các đơn hàng được tạo bởi admin này.
     */
    public function createdOrders()
    {
        return $this->hasMany(Order::class, 'created_by_admin_id'); // [cite: 104]
    }
}