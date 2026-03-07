<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case PAID = 'paid';
    case PROCESSING = 'processing';
    case SHIPPED = 'shipping';
    case COMPLETED = 'completed';
    case CANCELLED = 'canceled';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Chờ xác nhận',
            self::PAID => 'Đã thanh toán',
            self::PROCESSING => 'Đang xử lý',
            self::SHIPPED => 'Đã giao',
            self::COMPLETED => 'Hoàn thành',
            self::CANCELLED => 'Đã hủy',
        };
    }
}
