<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerAddress extends Model
{
    use HasFactory;

    protected $table = 'customer_addresses';

    protected $fillable = [
        'customer_id',
        'full_name',
        'phone',
        'address_line',
        'province_id',
        'district_id',
        'ward_id',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    /**
     * Lấy thông tin khách hàng sở hữu địa chỉ này.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Lấy thông tin Tỉnh/Thành phố.
     */
    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    /**
     * Lấy thông tin Quận/Huyện.
     */
    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    /**
     * Lấy thông tin Phường/Xã.
     */
    public function ward(): BelongsTo
    {
        return $this->belongsTo(Ward::class);
    }
}