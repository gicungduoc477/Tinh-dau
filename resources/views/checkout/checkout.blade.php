@extends('layouts.app')

@section('title', 'Thanh toán đơn hàng')

@section('content')
<style>
    .checkout-card { border-radius: 15px; transition: all 0.3s ease; }
    .payment-option {
        border: 2px solid #f8f9fa;
        border-radius: 10px;
        padding: 15px;
        cursor: pointer;
        transition: all 0.2s;
        display: block;
    }
    .payment-option:hover { border-color: #d1e7dd; background-color: #f8fffb; }
    .form-check-input:checked + .payment-option { border-color: #198754; background-color: #f0fdf4; }
    .cursor-pointer { cursor: pointer; }
    .sticky-summary { position: -webkit-sticky; position: sticky; top: 20px; }
</style>

<div class="container mt-5 mb-5">
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 rounded-3" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-octagon-fill fs-4 me-3"></i>
                <div>{{ session('error') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-alert="dismiss" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('checkout.place') }}" method="POST" id="checkout-form">
        @csrf
        <div class="row g-4">
            {{-- Cột bên trái: Thông tin khách hàng --}}
            <div class="col-lg-8">
                <div class="card p-4 shadow-sm border-0 checkout-card">
                    <h4 class="mb-4 text-dark fw-bold d-flex align-items-center">
                        <span class="bg-primary text-white rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 35px; height: 35px; font-size: 16px;">1</span>
                        Thông tin nhận hàng
                    </h4>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-muted text-uppercase">Họ tên người nhận</label>
                            <input type="text" name="full_name" class="form-control form-control-lg border-2" value="{{ old('full_name', Auth::user()->name ?? '') }}" required placeholder="Ví dụ: Nguyễn Văn A">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-muted text-uppercase">Số điện thoại</label>
                            <input type="tel" name="phone" class="form-control form-control-lg border-2" value="{{ old('phone') }}" required placeholder="09xxxxxxxx">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted text-uppercase">Địa chỉ Email</label>
                        <input type="email" name="email" class="form-control form-control-lg border-2" value="{{ old('email', Auth::user()->email ?? '') }}" required placeholder="email@example.com">
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold small text-muted text-uppercase">Địa chỉ giao hàng cụ thể</label>
                        <textarea name="address" class="form-control form-control-lg border-2" rows="3" required placeholder="Số nhà, tên đường, Phường/Xã, Quận/Huyện...">{{ old('address') }}</textarea>
                    </div>

                    <h4 class="mb-4 text-dark fw-bold d-flex align-items-center mt-2">
                        <span class="bg-primary text-white rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 35px; height: 35px; font-size: 16px;">2</span>
                        Vận chuyển & Thanh toán
                    </h4>

                    <div class="mb-4">
                        <label class="form-label fw-bold small text-muted text-uppercase">Phương thức vận chuyển</label>
                        <select name="shipping_method" id="shipping_method" class="form-select form-select-lg border-2 border-primary-subtle shadow-sm">
                            <option value="standard" selected>Giao hàng tiêu chuẩn (Miễn phí)</option>
                            <option value="express">Giao hàng hỏa tốc (+20.000 đ)</option>
                        </select>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <input class="form-check-input d-none" type="radio" name="payment_method" id="pm_cod" value="cod" checked>
                            <label class="payment-option cursor-pointer" for="pm_cod">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-cash-stack fs-2 me-3 text-success"></i>
                                    <div>
                                        <div class="fw-bold">Tiền mặt (COD)</div>
                                        <div class="small text-muted">Thanh toán khi nhận hàng</div>
                                    </div>
                                </div>
                            </label>
                        </div>
                        <div class="col-md-6">
                            <input class="form-check-input d-none" type="radio" name="payment_method" id="pm_bank" value="bank">
                            <label class="payment-option cursor-pointer" for="pm_bank">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-qr-code-scan fs-2 me-3 text-primary"></i>
                                    <div>
                                        <div class="fw-bold text-primary">Chuyển khoản QR</div>
                                        <div class="small text-muted">Qua cổng PayOS an toàn</div>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Cột bên phải: Tóm tắt đơn hàng --}}
            <div class="col-lg-4">
                <div class="sticky-summary">
                    <div class="card p-4 shadow-sm border-0 checkout-card">
                        <h5 class="mb-3 fw-bold border-bottom pb-3">Tóm tắt đơn hàng</h5>
                        
                        <div class="summary-details">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Tạm tính</span>
                                <span class="fw-bold">{{ number_format($total, 0, ',', '.') }} đ</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Phí vận chuyển</span>
                                <span id="shipping_fee_display" class="text-success fw-bold">0 đ</span>
                            </div>
                            <hr class="my-3 opacity-10">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <span class="fs-5 fw-bold">Tổng thanh toán</span>
                                <span id="grand_total_display" class="fs-4 fw-bold text-danger">{{ number_format($total, 0, ',', '.') }} đ</span>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-success btn-lg shadow fw-bold py-3 rounded-pill" id="btn-submit" onclick="processOrder()">
                                <i class="bi bi-check2-circle me-2"></i> XÁC NHẬN ĐẶT HÀNG
                            </button>
                            <a href="{{ route('cart.index') }}" class="btn btn-link text-muted btn-sm text-decoration-none">
                                <i class="bi bi-arrow-left me-1"></i> Quay lại giỏ hàng
                            </a>
                        </div>
                    </div>

                    <div class="mt-4 p-3 bg-white rounded-3 shadow-sm border-0 text-center small text-muted">
                        <i class="bi bi-shield-check text-success me-1"></i> Thông tin của bạn luôn được bảo mật tuyệt đối.
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    // Khởi tạo các biến tiền tệ từ PHP
    const baseTotal = {{ (int)$total }};
    
    function formatMoney(n) {
        return n.toLocaleString('vi-VN') + ' đ';
    }

    // Lắng nghe sự kiện thay đổi phí ship
    const shippingSelect = document.getElementById('shipping_method');
    const shippingFeeText = document.getElementById('shipping_fee_display');
    const grandTotalText = document.getElementById('grand_total_display');

    shippingSelect.addEventListener('change', function() {
        const fee = (this.value === 'express') ? 20000 : 0;
        
        // Cập nhật giao diện
        shippingFeeText.textContent = formatMoney(fee);
        grandTotalText.textContent = formatMoney(baseTotal + fee);
        
        // Hiệu ứng đổi màu nếu có phí
        if(fee > 0) {
            shippingFeeText.classList.replace('text-success', 'text-dark');
        } else {
            shippingFeeText.classList.replace('text-dark', 'text-success');
        }
    });

    function processOrder() {
        const btn = document.getElementById('btn-submit');
        const form = document.getElementById('checkout-form');
        const paymentMethodChecked = document.querySelector('input[name="payment_method"]:checked');

        // 1. Kiểm tra phương thức thanh toán
        if (!paymentMethodChecked) {
            Swal.fire('Thông báo', 'Vui lòng chọn phương thức thanh toán!', 'warning');
            return;
        }

        // 2. Kiểm tra validation form (Địa chỉ, tên, sđt...)
        if(!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        // 3. Hiệu ứng Loading
        const originalContent = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> ĐANG XỬ LÝ...';
        btn.disabled = true;

        // 4. Quyết định Action dựa trên Payment Method
        const paymentMethod = paymentMethodChecked.value;
        if (paymentMethod === 'bank') {
            form.action = "{{ route('payment.create') }}";
        } else {
            form.action = "{{ route('checkout.place') }}";
        }
        
        // 5. Gửi form
        form.submit();
    }
</script>
@endpush