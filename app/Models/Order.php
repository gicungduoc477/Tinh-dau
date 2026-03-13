<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage; // Quan trọng để xử lý ảnh
use Carbon\Carbon;

class Order extends Model
{
    /**
     * Cấu hình thời gian giới hạn khiếu nại (ngày).
     */
    const RETURN_LIMIT_DAYS = 3;

    /**
     * Các phương thức thanh toán online hỗ trợ hoàn tiền tự động/bán tự động.
     */
    const PAYMENT_METHOD_ONLINE = ['payos', 'vnpay', 'momo', 'banking'];

    /**
     * Các trường có thể gán hàng loạt.
     */
    protected $fillable = [
        'user_id', 
        'order_code',
        'customer_name', 
        'customer_email',
        'phone_number',
        'shipping_address',
        'total_price', 
        'status', 
        'payment_method', 
        'payment_status', 
        'paid_at', 
        'shipping_method', 
        'shipping_fee', 
        'meta', 
        'return_reason', 
        'return_image',
        'return_note',
        'bank_name',        
        'account_number',   
        'account_holder'    
    ];

    /**
     * Ép kiểu dữ liệu khi lấy ra từ DB.
     */
    protected $casts = [
        'meta' => 'array',
        'paid_at' => 'datetime',
        'total_price' => 'double',
        'shipping_fee' => 'double',
    ];

    /**
     * Danh sách trạng thái hiển thị tiếng Việt.
     * CẬP NHẬT: Thêm trạng thái trung gian 'returning_confirmed'
     */
    public static array $statuses = [
        'pending'             => 'Chờ xác nhận',    
        'paid'                => 'Đã thanh toán',   
        'confirmed'           => 'Đã xác nhận',     
        'shipping'            => 'Đang giao hàng',
        'success'             => 'Giao hàng thành công', 
        'returning'           => 'Đang khiếu nại', 
        'returning_confirmed' => 'Chờ nhận hàng hoàn', // Đã bấm đồng ý, chờ quét mã
        'returned'            => 'Đã trả hàng',      
        'refunding'           => 'Đang hoàn tiền',   
        'refunded'            => 'Đã hoàn tiền',     
        'canceled'            => 'Đã hủy',          
    ];

    // =========================================================================
    // LOGIC NGHIỆP VỤ (BUSINESS LOGIC)
    // =========================================================================

    /**
     * Kiểm tra đơn hàng có cần thực hiện quy trình hoàn tiền không.
     * CẬP NHẬT: Cho phép trạng thái trung gian 'returning_confirmed' xuất hiện trong Refund List
     */
    public function needsRefund(): bool
    {
        return $this->payment_status === 'paid' && 
               in_array($this->status, ['canceled', 'returning_confirmed', 'returned', 'refunding']) &&
               in_array(strtolower($this->payment_method), self::PAYMENT_METHOD_ONLINE);
    }

    /**
     * Kiểm tra điều kiện khiếu nại/trả hàng (Chỉ đơn thành công và trong hạn 3 ngày).
     */
    public function canBeReturned(): bool
    {
        if ($this->status !== 'success') return false;
        
        $expiryDate = $this->updated_at->copy()->addDays(self::RETURN_LIMIT_DAYS);
        return Carbon::now()->lessThanOrEqualTo($expiryDate);
    }

    // =========================================================================
    // QUAN HỆ (RELATIONSHIPS)
    // =========================================================================

    public function items(): HasMany { return $this->hasMany(OrderItem::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function statusHistories(): HasMany 
    { 
        return $this->hasMany(OrderStatusHistory::class)->orderBy('created_at', 'desc'); 
    }

    // =========================================================================
    // TRÌNH TRÍCH XUẤT (ACCESSORS & MUTATORS)
    // =========================================================================

    /**
     * Accessor xử lý đường dẫn ảnh khiếu nại.
     */
    public function getReturnImageUrlAttribute(): ?string
    {
        if (!$this->return_image) return null;

        if (filter_var($this->return_image, FILTER_VALIDATE_URL)) {
            return $this->return_image;
        }

        if (Storage::disk('public')->exists($this->return_image)) {
            return asset('storage/' . $this->return_image);
        }

        return "https://placehold.co/600x400?text=Image+Not+Found";
    }

    /**
     * Lấy nhãn trạng thái tiếng Việt.
     */
    public function getStatusLabelAttribute(): string
    {
        return self::$statuses[$this->status] ?? $this->status;
    }

    /**
     * Lấy màu sắc tương ứng với trạng thái.
     * CẬP NHẬT: Thêm màu cho trạng thái trung gian
     */
    public function getStatusColorAttribute(): string
    {
        $colors = [
            'pending'             => 'warning',
            'paid'                => 'info',
            'confirmed'           => 'primary',
            'shipping'            => 'info',
            'success'             => 'success',
            'returning'           => 'danger',
            'returning_confirmed' => 'warning', // Màu vàng/cam chờ xử lý
            'returned'            => 'dark',
            'refunding'           => 'warning',
            'refunded'            => 'secondary', 
            'canceled'            => 'danger',    
        ];
        return $colors[$this->status] ?? 'secondary';
    }

    /**
     * Tự động viết hoa tên chủ tài khoản ngân hàng.
     */
    public function setAccountHolderAttribute($value)
    {
        $this->attributes['account_holder'] = mb_strtoupper($value, 'UTF-8');
    }

    /**
     * Tạo link VietQR nhanh cho Admin thực hiện hoàn tiền.
     */
    public function getQrRefundUrlAttribute(): ?string
    {
        if (!$this->account_number || !$this->bank_name) return null;

        $bank = str_replace(' ', '', strtolower($this->bank_name));
        $amount = (int)$this->total_price;
        $info = "Hoan tien don " . $this->order_code;
        
        return "https://img.vietqr.io/image/{$bank}-{$this->account_number}-compact.jpg?amount={$amount}&addInfo=" . urlencode($info) . "&accountName=" . urlencode($this->account_holder);
    }

    /**
     * Lấy mã giao dịch từ cột meta.
     */
    public function getTransactionIdAttribute(): ?string
    {
        return $this->meta['transaction_id'] ?? ($this->meta['payment_id'] ?? 'N/A');
    }

    /**
     * Lấy tên khách hàng an toàn.
     */
    public function getSafeCustomerNameAttribute(): string
    {
        return $this->customer_name ?? ($this->user->name ?? 'Khách vãng lai');
    }
}