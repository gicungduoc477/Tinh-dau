<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $table = 'carts';

    protected $fillable = [
        'user_id',
        'product_id',
        'quantity',
        'price',
        'purchase_mode' // BẮT BUỘC thêm dòng này để lưu chế độ mua (once/subscription)
    ];

    /**
     * Mối quan hệ với Product.
     * Dùng withDefault để tránh lỗi sập trang nếu sản phẩm bị xóa khỏi hệ thống.
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id')->withDefault([
            'name' => 'Sản phẩm đã ngừng kinh doanh',
            'price' => 0,
            'image' => 'default.png'
        ]);
    }

    /**
     * Mối quan hệ với User.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Tính tổng tiền của dòng sản phẩm này.
     * Có thể gọi trong code qua: $cartItem->total_price
     */
    public function getTotalPriceAttribute()
    {
        return (float)$this->quantity * (float)$this->price;
    }
}