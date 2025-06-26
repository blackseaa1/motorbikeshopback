<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class CustomerSavedPaymentMethod
 *
 * @package App\Models
 *
 * @property int $id
 * @property int $customer_id
 * @property int $payment_method_id
 * @property bool $is_default
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \App\Models\Customer $customer
 * @property-read \App\Models\PaymentMethod $paymentMethod
 */
class CustomerSavedPaymentMethod extends Model
{
    use HasFactory;

    /**
     * Tên bảng trong cơ sở dữ liệu mà model này đại diện.
     *
     * @var string
     */
    protected $table = 'customer_saved_payment_methods';

    /**
     * Các thuộc tính có thể được gán hàng loạt.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'customer_id',
        'payment_method_id',
        'is_default',
    ];

    /**
     * Các thuộc tính nên được ép kiểu.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_default' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Lấy thông tin khách hàng đã lưu phương thức thanh toán này.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Lấy thông tin phương thức thanh toán được lưu.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }
}
