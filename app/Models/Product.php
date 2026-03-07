<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = ['name', 'slug', 'price', 'description', 'category_id', 'image', 'stock', 'classification'];

    protected static function booted()
    {
        static::creating(function ($product) {
            if (empty($product->slug)) {
                $slug = Str::slug($product->name);
                $original = $slug;
                $i = 1;
                while (self::where('slug', $slug)->exists()) {
                    $slug = $original . '-' . $i++;
                }
                $product->slug = $slug;
            }
        });
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Thiết lập quan hệ với Review
     */
    public function reviews()
    {
        return $this->hasMany(Review::class)->where('status', 1)->latest();
    }

    /**
     * Tính điểm trung bình (Ví dụ: 4.5 sao)
     */
    public function averageRating()
    {
        return round($this->reviews()->avg('rating'), 1) ?: 0;
    }
}