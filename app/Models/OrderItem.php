<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model; // Sử dụng Model thay vì Relations\Pivot nếu nó có logic riêng nhiều
use Illuminate\Database\Eloquent\Relations\Pivot;


// Nếu OrderItem chỉ đơn thuần là bảng trung gian không có logic/timestamp riêng đặc thù
// và bạn chỉ muốn truy cập qua $order->products() hoặc $product->orders() (cần định nghĩa belongsToMany)
// thì có thể không cần model này. Nhưng nếu bạn muốn quản lý chi tiết từng item, giá lúc mua, v.v. thì model này hữu ích.
// Dựa trên schema có 'price' tại thời điểm đặt và timestamps riêng, việc có model OrderItem là hợp lý.

class OrderItem extends Pivot // Hoặc Model nếu bạn không xem nó thuần túy là pivot trong mọi ngữ cảnh
{
    use HasFactory;

    protected $table = 'order_items'; // [cite: 68]
    public $incrementing = true; // Vì chúng ta dùng primary key phức hợp, không phải auto-increment đơn lẻ truyền thống

    // Chỉ định khóa chính phức hợp (Laravel không hỗ trợ trực tiếp cho Eloquent ORM một cách dễ dàng như khóa đơn)
    // Thông thường, bạn sẽ không cần đặt $primaryKey nếu dùng các phương thức quan hệ.
    // Nếu bạn cần tìm OrderItem bằng khóa phức hợp, bạn sẽ dùng where clauses.
    // protected $primaryKey = ['order_id', 'product_id']; // Laravel không hỗ trợ primary key dạng mảng trực tiếp.

    protected $fillable = [
        'order_id', // [cite: 69]
        'product_id', // [cite: 69]
        'quantity', // [cite: 69]
        'price', // [cite: 69]
    ];

    protected $casts = [
        'quantity' => 'integer', // [cite: 69]
        'price' => 'decimal:2', // [cite: 69]
    ];

    /**
     * Đơn hàng mà chi tiết này thuộc về.
     */
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id'); // [cite: 69]
    }

    /**
     * Sản phẩm trong chi tiết đơn hàng này.
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id'); // [cite: 69]
    }
}