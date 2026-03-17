<div class="card shadow-sm border-0 rounded-3 overflow-hidden" style="max-width: 600px; margin: 20px auto;">
    <div class="card-header bg-primary bg-gradient text-white text-center py-4">
        <i class="fas fa-truck-moving fa-3x mb-3"></i>
        <h2 class="h4 mb-0 fw-bold text-uppercase" style="letter-spacing: 1px;">
            Đơn hàng #{{ $order->order_code }}
        </h2>
        <p class="mb-0 opacity-75">Đang trên đường đến với bạn!</p>
    </div>
    
    <div class="card-body p-4">
        <div class="text-center mb-4">
            <div class="spinner-grow text-primary" role="status" style="width: 1rem; height: 1rem;"></div>
            <span class="ms-2 fw-medium text-secondary">Shipper đang nỗ lực giao hàng...</span>
        </div>

        <div class="d-flex align-items-start mb-4 bg-light p-3 rounded-3">
            <div class="text-primary me-3">
                <i class="fas fa-map-marker-alt fa-lg"></i>
            </div>
            <div>
                <strong class="d-block text-dark mb-1">Địa chỉ nhận hàng:</strong>
                <span class="text-muted small">{{ $order->shipping_address }}</span>
            </div>
        </div>

        <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center">
            <i class="fas fa-phone-alt me-3 fa-beat"></i>
            <div>
                <strong>Lưu ý:</strong> Vui lòng chú ý điện thoại để không bỏ lỡ cuộc gọi từ nhân viên bưu tá.
            </div>
        </div>
    </div>

    <div class="card-footer bg-white border-top-0 p-4 text-center">
        <a href="{{ route('orders.show', $order->id) }}" class="btn btn-outline-primary rounded-pill px-4">
            Theo dõi hành trình đơn hàng
        </a>
    </div>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">