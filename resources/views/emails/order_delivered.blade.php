@component('mail::message')
# Giao hàng thành công!

Chào **{{ $order->full_name }}**,

Chúng tôi rất vui mừng thông báo rằng đơn hàng **#{{ $order->order_code }}** của bạn đã được giao đến nơi thành công.

**Tóm tắt đơn hàng:**
- **Tổng cộng:** {{ number_format($order->total_price, 0, ',', '.') }}đ
- **Phương thức thanh toán:** {{ strtoupper($order->payment_method) }}
- **Địa chỉ nhận:** {{ $order->shipping_address }}

Cảm ơn bạn đã lựa chọn sản phẩm từ **NatureShop**. Hy vọng bạn sẽ hài lòng với món quà từ thiên nhiên này!

@component('mail::button', ['url' => route('orders.show', $order->id)])
Xem chi tiết và Đánh giá sản phẩm
@endcomponent

Trân trọng,<br>
Đội ngũ {{ config('app.name') }}
@endcomponent