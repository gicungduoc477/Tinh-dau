<div class="card shadow-lg border-0 rounded-4 overflow-hidden" style="max-width: 550px; margin: 30px auto; border-top: 5px solid #198754 !important;">
    <div class="card-body p-5">
        <div class="text-center mb-4">
            <div class="display-1 text-success mb-3">
                <i class="fas fa-check-circle"></i>
            </div>
            <h2 class="fw-bold text-dark">Hoàn tiền thành công!</h2>
            <p class="text-muted">Yêu cầu hoàn tiền cho đơn hàng của bạn đã được xử lý</p>
        </div>

        <div class="bg-light rounded-3 p-4 mb-4">
            <div class="d-flex justify-content-between mb-2">
                <span class="text-secondary">Mã đơn hàng:</span>
                <span class="fw-bold text-dark">#{{ $order->order_code }}</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span class="text-secondary">Trạng thái:</span>
                <span class="badge bg-success-soft text-success border border-success px-3">Đã hoàn trả</span>
            </div>
            <hr class="my-3 opacity-10">
            <div class="d-flex justify-content-between align-items-center">
                <span class="text-secondary">Số tiền hoàn lại:</span>
                <span class="h4 mb-0 fw-bold text-success">{{ number_format($order->total_price) }}đ</span>
            </div>
        </div>

        <div class="d-flex align-items-center p-3 rounded-3 border border-info bg-info bg-opacity-10 mb-4">
            <i class="fas fa-info-circle text-info me-3 fa-lg"></i>
            <div class="small text-dark">
                Quý khách vui lòng kiểm tra số dư trong <strong>tài khoản ngân hàng</strong> hoặc <strong>ví điện tử</strong> đã dùng để thanh toán. Thời gian nhận tiền thực tế có thể phụ thuộc vào ngân hàng của bạn.
            </div>
        </div>

        <div class="d-grid gap-2">
            <a href="{{ route('orders.show', $order->id) }}" class="btn btn-dark btn-lg rounded-pill shadow-sm">
                Xem chi tiết đơn hàng
            </a>
            <a href="/" class="btn btn-link text-decoration-none text-muted">Về trang chủ</a>
        </div>
    </div>
    
    <div class="card-footer bg-light border-0 py-3 text-center">
        <small class="text-muted italic">Cảm ơn bạn đã đồng hành cùng Nature Shop!</small>
    </div>
</div>

<style>
    .bg-success-soft { background-color: rgba(25, 135, 84, 0.1); }
    .fa-check-circle { animation: scaleIn 0.5s ease-out; }
    @keyframes scaleIn {
        0% { transform: scale(0); opacity: 0; }
        100% { transform: scale(1); opacity: 1; }
    }
</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">