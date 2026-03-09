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
        'purchase_mode' // Lưu chế độ mua: once hoặc subscription
    ];

    /**
     * Mối quan hệ với Product.
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
     * Accessor tính tổng tiền: $cartItem->total_price
     */
    public function getTotalPriceAttribute()
    {
        return (float)$this->quantity * (float)$this->price;
    }
}