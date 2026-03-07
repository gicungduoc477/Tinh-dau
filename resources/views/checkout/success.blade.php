@extends('layouts.app')

@section('title', 'Đặt hàng thành công')

@section('content')
<div class="container mt-5">
    <div class="card p-4 text-center">
        <h3 class="mb-3">Cảm ơn bạn — Đơn hàng đã được tạo</h3>
        @if($order)
            <p>Mã đơn: <strong>#{{ $order->id }}</strong></p>
            <p>Trạng thái: <strong>{{ $order->status_label }}</strong></p>
            @php $meta = session('guest_order_meta', []); @endphp
            @if(!empty($meta))
                <p>Giao hàng: <strong>{{ $meta['shipping_method'] === 'express' ? 'Giao hỏa tốc' : 'Giao tiêu chuẩn' }}</strong> ({{ number_format($meta['shipping_fee'] ?? 0,0,',','.') }} đ)</p>
                <p>Thanh toán: <strong>{{ $meta['payment_method'] === 'bank' ? 'Chuyển khoản' : 'COD' }}</strong></p>
            @endif
            <div class="d-flex justify-content-center gap-2">
                <a href="{{ route('orders.show', $order->id) }}" class="btn btn-outline-primary">Xem chi tiết đơn hàng</a>
                <a href="{{ route('home') }}" class="btn btn-primary">Tiếp tục mua sắm</a>
            </div>
        @else
            <p class="text-muted">Không tìm thấy thông tin đơn hàng.</p>
            <a href="{{ route('home') }}" class="btn btn-primary">Quay về cửa hàng</a>
        @endif
    </div>
</div>
@endsection
