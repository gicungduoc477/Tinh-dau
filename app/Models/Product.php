<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = [
        'name', 'slug', 'price', 'description', 
        'category_id', 'image', 'stock', 'classification'
    ];

    /**
     * Tự động tạo Slug khi tạo sản phẩm
     */
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

    /**
     * Accessor: Lấy URL ảnh đầy đủ
     * Ưu tiên: Link tuyệt đối (Cloudinary) > Link local > Ảnh mặc định
     */
    public function getImageUrlAttribute()
    {
        if (!$this->image) {
            return asset('backend/img/no-image.png');
        }

        // 1. Nếu đã là URL đầy đủ (Cloudinary http/https) thì trả về luôn
        if (Str::startsWith($this->image, ['http://', 'https://'])) {
            return $this->image;
        }

        // 2. Nếu là file tồn tại trong thư mục uploads/product (Local/Storage)
        $localPath = 'uploads/product/' . ltrim($this->image, '/');
        if (file_exists(public_path($localPath))) {
            return asset($localPath);
        }

        // 3. Dự phòng: Nếu là path của Cloudinary lưu thiếu domain (tinh_dau_shop/products/...)
        // Bạn có thể nối domain Cloudinary nếu muốn, hoặc trả về no-image
        return asset('backend/img/no-image.png');
    }

    /**
     * Thêm image_url vào dữ liệu khi convert model sang Array/JSON
     */
    protected $appends = ['image_url'];

    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Quan hệ với danh mục
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Quan hệ với đánh giá (Chỉ lấy đánh giá đã duyệt)
     */
    public function reviews()
    {
        // Eager load 'user' để tránh lỗi N+1 query khi hiển thị danh sách đánh giá
        return $this->hasMany(Review::class)->where('status', 1)->with('user')->latest();
    }

    /**
     * Tính điểm trung bình (Ví dụ: 4.5)
     */
    public function averageRating()
    {
        // Sử dụng cache hoặc gọi trực tiếp avg
        return round($this->reviews()->avg('rating'), 1) ?: 0;
    }

    /**
     * Đếm số lượng đánh giá
     */
    public function totalReviews()
    {
        return $this->reviews()->count();
    }
}