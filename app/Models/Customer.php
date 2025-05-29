<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Customer extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'customers';

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'img',
        'status',
    ];

    protected $hidden = [
        'password',
    ];

    /**
     * Các đơn hàng của khách hàng này.
     */
    public function orders()
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    /**
     * Các đánh giá của khách hàng này.
     */
    public function reviews()
    {
        return $this->hasMany(Review::class, 'customer_id');
    }

    /**
     * Giỏ hàng của khách hàng này.
     */
    public function cart()
    {
        return $this->hasOne(Cart::class, 'customer_id'); 
    }

    /**
     * Các bài blog được viết bởi khách hàng này.
     */
    public function blogPosts()
    {
        return $this->hasMany(BlogPost::class, 'author_id');
    }}