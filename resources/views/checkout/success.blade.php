@extends('layouts.app')

@section('title', 'Đặt hàng thành công')

@section('content')
<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card p-4 text-center shadow-sm border-0 rounded-4">
                <div class="mb-3">
                    <i class="fas fa-check-circle text-success fa-4x"></i>
                </div>
                <h3 class="mb-3 fw-bold">Cảm ơn bạn — Đơn hàng đã được tạo</h3>
                
                @if($order)
                    <p class="mb-1 text-muted">Mã đơn hàng: <strong class="text-primary">#{{ $order->order_code ?? $order->id }}</strong></p>
                    <p class="mb-4">Trạng thái: <span class="badge bg-warning text-dark">{{ $order->status_label ?? 'Đang chờ xử lý' }}</span></p>

                    {{-- HIỂN THỊ QR NẾU CHỌN CHUYỂN KHOẢN --}}
                    @if($order->payment_method === 'bank')
                        <div class="alert alert-light border p-4 mb-4 rounded-4 shadow-sm">
                            <h5 class="text-danger fw-bold mb-3"><i class="fas fa-university me-2"></i>THANH TOÁN CHUYỂN KHOẢN</h5>
                            
                            <div class="mb-3">
                                <img src="{{ $qrImageUrl }}" alt="Mã QR" class="img-fluid rounded border shadow-sm" style="max-width: 250px;">
                            </div>

                            <div class="d-md-none mb-3">
                                <a href="{{ $qrImageUrl }}" download="QR_NatureShop_{{ $order->id }}.jpg" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-download me-1"></i> Tải mã QR về máy
                                </a>
                            </div>

                            <div class="text-start mx-auto" style="max-width: 320px;">
                                <p class="mb-1">Số tiền: <strong class="fs-5">{{ number_format($order->total_price, 0, ',', '.') }}đ</strong></p>
                                <p class="mb-0">Nội dung: <strong class="text-primary">NatureShop{{ $order->id }}</strong></p>
                            </div>

                            <div class="mt-3 p-2 bg-warning bg-opacity-10 rounded">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle"></i> Bạn hãy chụp màn hình hoặc tải ảnh QR, sau đó mở App Ngân hàng quét từ thư viện ảnh nhé.
                                </small>
                            </div>
                        </div>
                    @endif

                    <div class="d-flex justify-content-center gap-2 mt-2">
                        <a href="{{ route('home') }}" class="btn btn-outline-secondary px-4">Tiếp tục mua hàng</a>
                        <a href="{{ Auth::check() ? route('orders.show', $order->id) : '#' }}" class="btn btn-primary px-4 shadow">Chi tiết đơn hàng</a>
                    </div>
                @else
                    <p class="text-muted py-5">Không tìm thấy thông tin đơn hàng.</p>
                    <a href="{{ route('home') }}" class="btn btn-primary px-5">Quay về cửa hàng</a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection