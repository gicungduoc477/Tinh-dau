<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;

class OrderStatusHistory extends Model
{
    use HasFactory;

    /**
     * Tên bảng trong cơ sở dữ liệu.
     */
    protected $table = 'order_status_histories';

    /**
     * Các trường có thể gán hàng loạt (Mass Assignment).
     */
    protected $fillable = [
        'order_id', 
        'from_status', 
        'to_status', 
        'user_id', 
        'note'
    ];

    // =========================================================================
    // QUAN HỆ (RELATIONSHIPS)
    // =========================================================================

    /**
     * Liên kết ngược lại với đơn hàng (Order).
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Liên kết với người thực hiện thay đổi (Admin/User).
     * Mặc định sử dụng user_id để liên kết với bảng users.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // =========================================================================
    // TRÌNH TRÍCH XUẤT (ACCESSORS) - Cú pháp mới của Laravel
    // =========================================================================

    /**
     * Lấy nhãn tiếng Việt của trạng thái cũ.
     * Cách dùng: $history->from_status_label
     */
    protected function fromStatusLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => Order::$statuses[$this->from_status] ?? ($this->from_status ?? 'Bắt đầu'),
        );
    }

    /**
     * Lấy nhãn tiếng Việt của trạng thái mới.
     * Cách dùng: $history->to_status_label
     */
    protected function toStatusLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => Order::$statuses[$this->to_status] ?? $this->to_status,
        );
    }

    /**
     * Lấy tên người thực hiện một cách an toàn.
     * Cách dùng: $history->executor_name
     */
    protected function executorName(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->user ? $this->user->name : 'Hệ thống',
        );
    }
}