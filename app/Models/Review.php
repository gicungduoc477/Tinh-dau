<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'product_id', 
        'rating', 
        'comment', 
        'image', 
        'image_list', 
        'video',
        'tags',
        'reply',      
        'reply_at',   
        'first_reply_at',
        'admin_note',
        'is_resolved',
        'status'
    ];

    protected $casts = [
        'reply_at' => 'datetime',
        'first_reply_at' => 'datetime',
        'image_list' => 'array',
        'tags' => 'array',
        'is_resolved' => 'boolean',
    ];

    // Trả về trạng thái xử lý dựa trên cột is_resolved
    public function getIsResolvedStatusAttribute()
    {
        return $this->is_resolved ? 'Đã xử lý' : 'Chờ phản hồi';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Accessor: URL ảnh chính
     */
    public function getImageUrlAttribute()
    {
        if (!$this->image) return asset('images/no-image.png');
        if (filter_var($this->image, FILTER_VALIDATE_URL)) return $this->image;
        return Storage::disk('public')->exists($this->image) 
            ? Storage::url($this->image) 
            : asset('images/no-image.png');
    }

    /**
     * Accessor: URL Video
     */
    public function getVideoUrlAttribute()
    {
        if (!$this->video) return null;
        if (filter_var($this->video, FILTER_VALIDATE_URL)) return $this->video;
        return Storage::url($this->video);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}