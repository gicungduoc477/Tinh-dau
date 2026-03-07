@extends('layouts.app')

@section('title', 'Thanh toán')

@section('content')
<div class="container mt-5 mb-5">
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Form mặc định trỏ về checkout.place (COD). JavaScript sẽ tự đổi action nếu chọn Bank --}}
    <form action="{{ route('checkout.place') }}" method="POST" id="checkout-form">
        @csrf
        <div class="row">
            <div class="col-lg-8">
                <div class="card p-4 shadow-sm border-0 rounded-3 mb-4">
                    <h4 class="mb-4 text-primary fw-bold">
                        <i class="fas fa-shipping-fast me-2"></i>Thông tin nhận hàng
                    </h4>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-uppercase">Họ tên người nhận</label>
                            {{-- Đổi name="name" thành name="full_name" để khớp với Controller --}}
                            <input type="text" name="full_name" class="form-control form-control-lg" value="{{ old('full_name', Auth::user()->name ?? '') }}" required placeholder="Nhập đầy đủ họ tên">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-uppercase">Số điện thoại</label>
                            <input type="text" name="phone" class="form-control form-control-lg" value="{{ old('phone') }}" required placeholder="Số điện thoại liên hệ">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase">Email</label>
                        <input type="email" name="email" class="form-control form-control-lg" value="{{ old('email', Auth::user()->email ?? '') }}" required placeholder="email@example.com">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase">Địa chỉ giao hàng</label>
                        <textarea name="address" class="form-control form-control-lg" rows="3" required placeholder="Số nhà, tên đường...">{{ old('address') }}</textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold small text-uppercase">Phương thức giao hàng</label>
                        <select name="shipping_method" id="shipping_method" class="form-select form-select-lg border-primary">
                            <option value="standard">Giao tiêu chuẩn (Miễn phí)</option>
                            <option value="express">Giao hỏa tốc (+20.000 đ)</option>
                        </select>
                    </div>

                    <div class="p-3 bg-light rounded-3 border">
                        <label class="form-label fw-bold d-block mb-3 small text-uppercase">
                            <i class="fas fa-wallet me-2"></i>Phương thức thanh toán
                        </label>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="payment_method" id="pm_cod" value="cod" checked>
                            <label class="form-check-label cursor-pointer font-medium" for="pm_cod">
                                Thanh toán khi nhận hàng (COD)
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" id="pm_bank" value="bank">
                            <label class="form-check-label cursor-pointer font-medium text-primary fw-bold" for="pm_bank">
                                Chuyển khoản ngân hàng (Qua mã QR PayOS)
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="sticky-top" style="top: 20px;">
                    <div class="card p-4 shadow-sm border-0 rounded-3">
                        <h5 class="mb-3 fw-bold border-bottom pb-2">Đơn hàng của bạn</h5>
                        
                        <div class="summary-details">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Tạm tính</span>
                                <span class="fw-bold">{{ number_format($total, 0, ',', '.') }} đ</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Phí vận chuyển</span>
                                <span id="shipping_fee" class="text-success fw-bold">0 đ</span>
                            </div>
                            <hr class="my-3">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <span class="fs-5 fw-bold">Tổng cộng</span>
                                <span id="grand_total" class="fs-4 fw-bold text-danger">{{ number_format($total, 0, ',', '.') }} đ</span>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="button" class="btn btn-success btn-lg shadow fw-bold py-3" id="btn-submit" onclick="processOrder()">
                                XÁC NHẬN ĐẶT HÀNG
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    // Lấy tổng tiền từ Server sang JS
    const baseTotal = {{ (int)$total }};
    function formatMoney(n){ return n.toLocaleString('vi-VN') + ' đ'; }

    // Xử lý thay đổi phí vận chuyển giao diện
    document.getElementById('shipping_method').addEventListener('change', function() {
        const fee = (this.value === 'express') ? 20000 : 0;
        document.getElementById('shipping_fee').textContent = formatMoney(fee);
        document.getElementById('grand_total').textContent = formatMoney(baseTotal + fee);
    });

    function processOrder() {
        const btn = document.getElementById('btn-submit');
        const form = document.getElementById('checkout-form');
        const paymentMethodChecked = document.querySelector('input[name="payment_method"]:checked');

        if (!paymentMethodChecked) {
            alert('Vui lòng chọn phương thức thanh toán');
            return;
        }

        const paymentMethod = paymentMethodChecked.value;

        // Kiểm tra validation của trình duyệt
        if(!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        // Hiệu ứng loading
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> ĐANG XỬ LÝ...';
        btn.disabled = true;

        // LOGIC QUAN TRỌNG: Đổi Action tùy theo phương thức thanh toán
        if (paymentMethod === 'bank') {
            form.action = "{{ route('payment.create') }}";
        } else {
            form.action = "{{ route('checkout.place') }}";
        }
        
        form.submit();
    }
</script>
@endpush