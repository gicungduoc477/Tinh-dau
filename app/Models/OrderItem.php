<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderItem extends Model
{
    use HasFactory;

    /**
     * Các trường có thể gán hàng loạt.
     */
    protected $fillable = [
        'order_id', 
        'product_id', 
        'quantity', 
        'price'
    ];

    /**
     * Ép kiểu dữ liệu để đảm bảo tính toán chính xác.
     */
    protected $casts = [
        'quantity' => 'integer',
        'price'    => 'double',
    ];

    /**
     * Liên kết tới đơn hàng (Order).
     * Rất quan trọng để hàm ReviewController::index() có thể lọc theo user_id và status.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Liên kết tới sản phẩm (Product).
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // =========================================================================
    // TRÌNH TRÍCH XUẤT (ACCESSORS)
    // =========================================================================

    /**
     * Tính thành tiền: $item->subtotal
     */
    public function getSubtotalAttribute(): float
    {
        return (float) ($this->price * $this->quantity);
    }

    /**
     * Lấy tên sản phẩm an toàn: $item->product_name
     */
    public function getProductNameAttribute(): string
    {
        return $this->product ? $this->product->name : 'Sản phẩm không còn tồn tại';
    }
}