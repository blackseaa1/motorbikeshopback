<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Customer extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'customers'; // [cite: 40]

    protected $fillable = [
        'name', // [cite: 41]
        'email', // [cite: 41]
        'password', // [cite: 41]
        'phone', // [cite: 41]
        'img', // [cite: 41]
    ];

    protected $hidden = [
        'password', // [cite: 41]
    ];

    /**
     * Các đơn hàng của khách hàng này.
     */
    public function orders()
    {
        return $this->hasMany(Order::class, 'customer_id'); // [cite: 67]
    }

    /**
     * Các đánh giá của khách hàng này.
     */
    public function reviews()
    {
        return $this->hasMany(Review::class, 'customer_id'); // [cite: 71]
    }

    /**
     * Giỏ hàng của khách hàng này.
     */
    public function cart()
    {
        return $this->hasOne(Cart::class, 'customer_id'); // [cite: 73]
    }

    /**
     * Các bài blog được viết bởi khách hàng này.
     */
    public function blogPosts()
    {
        return $this->hasMany(BlogPost::class, 'author_id'); // [cite: 77]
    }

}