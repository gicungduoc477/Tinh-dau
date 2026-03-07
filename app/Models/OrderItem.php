<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderItem extends Model
{
    use HasFactory;

    /**
     * Đảm bảo fillable khớp hoàn toàn với các trường được dùng trong CheckoutController.
     */
    protected $fillable = [
        'order_id', 
        'product_id', 
        'quantity', 
        'price'
    ];

    /**
     * Ép kiểu dữ liệu để khi tính toán không bị lỗi định dạng chuỗi.
     */
    protected $casts = [
        'quantity' => 'integer',
        'price'    => 'double',
    ];

    /**
     * Liên kết ngược lại đơn hàng (Order).
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Liên kết tới sản phẩm (Product) để lấy thông tin như: name, image, slug.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // =========================================================================
    // TRÌNH TRÍCH XUẤT (ACCESSORS)
    // =========================================================================

    /**
     * Tự động tính thành tiền cho từng item (Giá x Số lượng).
     * Cách dùng ở View: $item->subtotal
     */
    public function getSubtotalAttribute(): float
    {
        return $this->price * $this->quantity;
    }

    /**
     * Lấy tên sản phẩm an toàn ngay cả khi sản phẩm gốc bị xóa (nếu bạn không dùng soft delete).
     */
    public function getProductNameAttribute(): string
    {
        return $this->product ? $this->product->name : 'Sản phẩm không còn tồn tại';
    }
}