@extends('layouts.app')

@section('title', 'Đặt hàng thành công')

@section('content')
<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card p-4 shadow-sm border-0 rounded-4 text-center">
                <div class="mb-3">
                    <i class="fas fa-check-circle text-success fa-4x"></i>
                </div>
                <h3 class="mb-3 fw-bold">Cảm ơn bạn — Đơn hàng đã được tạo</h3>
                
                @if($order)
                    <p class="mb-1">Mã đơn: <strong class="text-primary">#{{ $order->order_code ?? $order->id }}</strong></p>
                    <p class="mb-3">Trạng thái: <span class="badge bg-warning text-dark">{{ $order->status_label ?? 'Đang chờ xử lý' }}</span></p>

                    {{-- PHẦN THANH TOÁN TỰ ĐỘNG --}}
                    @if($order->payment_method === 'bank')
                        <div class="alert alert-light border p-4 my-4 shadow-sm rounded-3">
                            <h5 class="text-danger fw-bold mb-3"><i class="fas fa-university me-2"></i>THANH TOÁN QUA NGÂN HÀNG</h5>
                            <p class="mb-3">Số tiền: <strong class="fs-3 text-dark">{{ number_format($order->total_price, 0, ',', '.') }} đ</strong></p>

                            <div class="d-md-none d-grid gap-2">
                                <a href="{{ $paymentLink }}" id="openBankApp" class="btn btn-success btn-lg py-3 fw-bold shadow-sm">
                                    <i class="fas fa-mobile-alt me-2"></i> BẤM ĐỂ MỞ APP NGÂN HÀNG
                                </a>
                                <small class="text-muted">Mở App ngân hàng, thông tin sẽ được tự động điền</small>
                            </div>

                            <div class="d-none d-md-block mt-3">
                                <p class="small text-muted mb-2">Dùng App Ngân hàng quét mã QR dưới đây:</p>
                                <div class="bg-white d-inline-block p-2 border rounded shadow-sm">
                                    <img src="{{ $qrImageUrl }}" alt="Mã QR Thanh toán" style="max-width: 280px; height: auto;">
                                </div>
                                <div class="mt-2 small text-secondary">
                                    Nội dung CK: <strong>NatureShop{{ $order->id }}</strong>
                                </div>
                            </div>

                            <div class="mt-3 p-2 bg-warning bg-opacity-10 rounded">
                                <small class="text-danger fw-bold">
                                    <i class="fas fa-info-circle"></i> Vui lòng giữ nguyên nội dung chuyển khoản để hệ thống tự động xác nhận đơn hàng.
                                </small>
                            </div>
                        </div>
                    @endif

                    <hr class="my-4">
                    
                    <div class="d-flex justify-content-center gap-3">
                        <a href="{{ route('home') }}" class="btn btn-outline-secondary px-4">Tiếp tục mua sắm</a>
                        <a href="{{ Auth::check() ? route('orders.show', $order->id) : '#' }}" class="btn btn-primary px-4 shadow">Xem chi tiết đơn hàng</a>
                    </div>
                @else
                    <div class="py-5">
                        <p class="text-muted">Không tìm thấy thông tin đơn hàng.</p>
                        <a href="{{ route('home') }}" class="btn btn-primary px-5">Quay về cửa hàng</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- SCRIPT HỖ TRỢ MỞ APP NHANH --}}
<script>
    document.getElementById('openBankApp')?.addEventListener('click', function(e) {
        // Một số trình duyệt Mobile cần "mồi" bằng giao thức vietqr://
        // Chúng ta tạo một iframe ẩn để thử kích hoạt App trước
        const deepLink = "vietqr://payment?{{ parse_url($paymentLink, PHP_URL_QUERY) }}";
        const iframe = document.createElement("iframe");
        iframe.style.display = "none";
        iframe.src = deepLink;
        document.body.appendChild(iframe);
        
        // Sau đó vẫn cho phép chuyển hướng đến link ảnh VietQR như bình thường để đảm bảo 100% hoạt động
        setTimeout(() => {
            document.body.removeChild(iframe);
        }, 500);
    });
</script>
@endsection