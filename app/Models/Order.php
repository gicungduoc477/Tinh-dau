<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'return_image' => 'array', // Laravel tự động convert JSON từ DB thành mảng PHP
    ];

    /**
     * Danh sách trạng thái hiển thị tiếng Việt.
     */
    public static array $statuses = [
        'pending'             => 'Chờ xác nhận',    
        'paid'                => 'Đã thanh toán',   
        'confirmed'           => 'Đã xác nhận',     
        'shipping'            => 'Đang giao hàng',
        'success'             => 'Giao hàng thành công', 
        'returning'           => 'Đang khiếu nại', 
        'returning_confirmed' => 'Chờ nhận hàng hoàn', 
        'returned'            => 'Đã trả hàng',      
        'refunding'           => 'Đang hoàn tiền',   
        'refunded'            => 'Đã hoàn tiền',     
        'canceled'            => 'Đã hủy',          
    ];

    /**
     * Chuẩn hóa định dạng ngày tháng khi convert sang mảng/JSON.
     */
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    // =========================================================================
    // LOGIC NGHIỆP VỤ (BUSINESS LOGIC)
    // =========================================================================

    /**
     * Kiểm tra đơn hàng có cần thực hiện quy trình hoàn tiền không.
     */
    public function needsRefund(): bool
    {
        return $this->payment_status === 'paid' && 
               in_array($this->status, ['canceled', 'returning_confirmed', 'returned', 'refunding']) &&
               in_array(strtolower($this->payment_method), self::PAYMENT_METHOD_ONLINE);
    }

    /**
     * Kiểm tra điều kiện khiếu nại/trả hàng.
     */
    public function canBeReturned(): bool
    {
        // 1. Nếu đơn hàng đã/đang trong quy trình khiếu nại thì KHÔNG cho gửi tiếp
        if (in_array($this->status, ['returning', 'returning_confirmed', 'returned', 'refunding', 'refunded'])) {
            return false;
        }

        // 2. Chỉ cho phép khi đang giao hoặc đã thành công
        if (!in_array($this->status, ['success', 'shipping'])) {
            return false;
        }

        // 3. Nếu đã thành công, kiểm tra giới hạn ngày (Mặc định 3 ngày)
        if ($this->status === 'success') {
            $baseDate = $this->updated_at ?? $this->created_at;
            $expiryDate = $baseDate->copy()->addDays(self::RETURN_LIMIT_DAYS);
            return Carbon::now()->lessThanOrEqualTo($expiryDate);
        }

        return true;
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
    // ACCESSORS & MUTATORS
    // =========================================================================

    /**
     * Luôn đảm bảo return_image trả về mảng (tránh lỗi foreach trên View)
     */
    public function getReturnImageAttribute($value)
    {
        return is_null($value) ? [] : json_decode($value, true);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::$statuses[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        $colors = [
            'pending'             => 'warning',
            'paid'                => 'info',
            'confirmed'           => 'primary',
            'shipping'            => 'info',
            'success'             => 'success',
            'returning'           => 'danger',
            'returning_confirmed' => 'warning',
            'returned'            => 'dark',
            'refunding'           => 'warning',
            'refunded'            => 'secondary', 
            'canceled'            => 'danger',    
        ];
        return $colors[$this->status] ?? 'secondary';
    }

    public function setAccountHolderAttribute($value)
    {
        $this->attributes['account_holder'] = mb_strtoupper($value, 'UTF-8');
    }

    /**
     * Tạo link VietQR dành cho Admin quét mã hoàn tiền nhanh.
     */
    public function getQrRefundUrlAttribute(): ?string
    {
        if (!$this->account_number || !$this->bank_name) return null;

        $bank = str_replace(' ', '', $this->bank_name);
        $amount = (int)$this->total_price;
        $info = "Hoan tien don " . $this->order_code;
        
        return "https://img.vietqr.io/image/{$bank}-{$this->account_number}-compact.jpg?amount={$amount}&addInfo=" . urlencode($info) . "&accountName=" . urlencode($this->account_holder);
    }

    public function getTransactionIdAttribute(): ?string
    {
        if (empty($this->meta)) return 'N/A';
        return $this->meta['transaction_id'] ?? ($this->meta['payment_id'] ?? 'N/A');
    }

    public function getSafeCustomerNameAttribute(): string
    {
        return $this->customer_name ?: ($this->user->name ?? 'Khách vãng lai');
    }

    public function hasReturnImages(): bool
    {
        $images = $this->return_image;
        return is_array($images) && count($images) > 0;
    }
}