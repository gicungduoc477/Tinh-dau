<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'product_id', 'rating', 'comment', 
        'image', 'image_list', 'video', 'tags',
        'reply', 'reply_at', 'first_reply_at',
        'admin_note', 'is_resolved', 'status'
    ];

    protected $casts = [
        'reply_at' => 'datetime',
        'first_reply_at' => 'datetime',
        'image_list' => 'array',
        'tags' => 'array', 
        'is_resolved' => 'boolean',
    ];

    /**
     * Tự động gộp Tags và Comment khi hiển thị.
     * Sử dụng: $review->full_comment
     */
    public function getFullCommentAttribute()
    {
        $allContent = [];

        // 1. Lấy các tags (câu trả lời nhanh)
        if (!empty($this->tags) && is_array($this->tags)) {
            $allContent[] = implode(', ', $this->tags);
        }

        // 2. Lấy nội dung comment
        if (!empty($this->comment)) {
            $allContent[] = $this->comment;
        }

        return count($allContent) > 0 ? implode(' - ', $allContent) : 'Khách hàng không để lại bình luận';
    }

    /**
     * Xử lý URL Hình ảnh (Hỗ trợ cả Cloudinary URL và Local Storage)
     * Sử dụng: $review->image_url
     */
    public function getImageUrlAttribute()
    {
        if (!$this->image) return asset('images/no-image.png');

        // Nếu là URL từ Cloudinary hoặc bên ngoài
        if (filter_var($this->image, FILTER_VALIDATE_URL)) {
            return $this->image;
        }

        // Nếu là file lưu local trong storage/public
        return Storage::disk('public')->exists($this->image) 
            ? Storage::url($this->image) 
            : asset('images/no-image.png');
    }

    /**
     * Xử lý URL Video (Hỗ trợ cả Cloudinary URL và Local Storage)
     * Sử dụng: $review->video_url
     */
    public function getVideoUrlAttribute()
    {
        if (!$this->video) return null;

        // Nếu là URL từ Cloudinary
        if (filter_var($this->video, FILTER_VALIDATE_URL)) {
            return $this->video;
        }

        // Nếu là file lưu local
        return Storage::disk('public')->exists($this->video) 
            ? Storage::url($this->video) 
            : null;
    }

    /**
     * Kiểm tra xem review này có video hay không
     */
    public function hasVideo()
    {
        return !empty($this->video);
    }

    /**
     * Lấy trạng thái xử lý bằng văn bản
     */
    public function getIsResolvedStatusAttribute()
    {
        return $this->is_resolved ? 'Đã phản hồi' : 'Chờ xử lý';
    }

    /* --- Relationships --- */

    public function user() 
    { 
        return $this->belongsTo(User::class); 
    }

    public function product() 
    { 
        return $this->belongsTo(Product::class); 
    }

    /* --- Scopes --- */

    /**
     * Chỉ lấy các đánh giá đang hiển thị (active)
     */
    public function scopeActive($query) 
    { 
        return $query->where('status', 'active'); 
    }

    /**
     * Lấy các đánh giá có kèm hình ảnh hoặc video
     */
    public function scopeHasMedia($query)
    {
        return $query->whereNotNull('image')->orWhereNotNull('video');
    }
}