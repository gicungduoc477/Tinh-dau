<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    const ROLE_ADMIN = 'admin';
    const ROLE_USER = 'user';

    protected $fillable = [
        'name', 
        'email', 
        'phone', 
        'password', 
        'role',
        'is_verified', 
        'verify_token', 
        'verify_token_expires_at', 
        'remember_token',
    ];

    protected $hidden = [
        'password', 
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'verify_token_expires_at' => 'datetime',
        'is_verified' => 'boolean',
    ];

    // --- RELATIONSHIPS ---
    public function orders(): HasMany { return $this->hasMany(Order::class); }
    public function reviews(): HasMany { return $this->hasMany(Review::class); }

    // --- LOGIC ---
    public function isAdmin(): bool { return $this->role === self::ROLE_ADMIN; }

    /**
     * Tìm người dùng bằng Email hoặc Số điện thoại
     */
    public static function findByEmailOrPhone(?string $identifier): ?self
    {
        if (!$identifier) return null;
        return self::where('email', $identifier)->orWhere('phone', $identifier)->first();
    }

    /**
     * Ghi đè hàm gửi mail Reset Password
     * FIX TRIỆT ĐỂ: Chỉ truyền token, để Laravel tự tạo URL chuẩn.
     */
    public function sendPasswordResetNotification($token)
    {
        // Khi chỉ truyền $token, Laravel Notification mặc định sẽ tự tạo Link:
        // APP_URL + /reset-password/ + $token + ?email=...
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * Xác thực tài khoản (Dùng cho quy trình đăng ký)
     */
    public static function verifyAccount(string $token): bool
    {
        $user = self::where('verify_token', $token)->first();
        
        if (!$user || ($user->verify_token_expires_at && $user->verify_token_expires_at->isPast())) {
            return false;
        }

        return $user->update([
            'is_verified' => true,
            'email_verified_at' => now(),
            'verify_token' => null,
            'verify_token_expires_at' => null,
        ]);
    }
}