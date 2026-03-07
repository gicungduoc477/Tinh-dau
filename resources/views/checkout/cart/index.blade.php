@extends('layouts.app')

@section('title', 'Giỏ hàng của bạn')

@section('content')
<div class="container mt-5 pb-5">
    @push('styles')
    <style>
        .cart-card { border-radius: 14px; box-shadow: 0 10px 30px rgba(18,38,23,0.06); overflow: hidden; background: #fff; }
        .cart-product-image { width:80px; height:80px; object-fit:cover; border-radius:8px; border: 1px solid #eee; background: #f9f9f9; }
        .cart-summary { border-radius:12px; background:#fff; box-shadow:0 6px 18px rgba(18,38,23,0.04); padding:25px; position: sticky; top: 20px; }
        .qty-input-field { width:65px; text-align: center; border: 1px solid #ced4da; border-radius: 4px; }
        .btn-delete-custom { color: #dc3545; border: 1px solid #f8d7da; background: #fff5f5; padding: 8px 12px; border-radius: 6px; transition: 0.3s; cursor: pointer; }
        .btn-delete-custom:hover { background: #dc3545; color: #fff; }
    </style>
    @endpush

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-dark"><i class="fas fa-shopping-basket me-2 text-success"></i>Giỏ hàng của bạn</h3>
        <a href="{{ route('products.index') }}" class="btn btn-outline-success btn-sm rounded-pill px-4">
            <i class="fas fa-arrow-left me-2"></i>Tiếp tục mua sắm
        </a>
    </div>

    <div id="cart-content">
        @if(empty($cart) || count($cart) == 0)
            <div class="card cart-card border-0 p-5 text-center shadow-sm">
                <div class="mb-4">
                    <i class="fas fa-shopping-cart fa-4x text-light"></i>
                </div>
                <h4 class="text-muted">Giỏ hàng hiện đang trống</h4>
                <div class="mt-2">
                    <a href="{{ route('products.index') }}" class="btn btn-success px-5 py-2 rounded-pill shadow-sm">MUA SẮM NGAY</a>
                </div>
            </div>
        @else
            <div class="row">
                <div class="col-lg-8">
                    <div class="card cart-card border-0 shadow-sm">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead class="table-light small text-uppercase">
                                    <tr>
                                        <th class="ps-4 py-3">Sản phẩm</th>
                                        <th class="text-end">Đơn giá</th>
                                        <th class="text-center">Số lượng</th>
                                        <th class="text-end">Thành tiền</th>
                                        <th class="text-center"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($cart as $id => $item)
                                    @php 
                                        $currentPrice = (float)($item['price'] ?? 0);
                                        $qty = (int)($item['quantity'] ?? 1);
                                        $itemTotal = $currentPrice * $qty;
                                    @endphp
                                    <tr data-id="{{ $id }}">
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <img src="{{ (!empty($item['image']) && file_exists(public_path('uploads/product/' . $item['image']))) ? asset('uploads/product/' . $item['image']) : asset('backend/img/no-image.png') }}" 
                                                     class="img-fluid rounded" style="width: 60px; height: 60px; object-fit: cover;">
                                                <div class="ms-3">
                                                    <div class="fw-bold text-dark">{{ $item['name'] ?? 'Sản phẩm' }}</div>
                                                    <small class="text-muted">Mã SP: #{{ $item['product_id'] ?? $id }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <span class="fw-bold text-dark">{{ number_format($currentPrice, 0, ',', '.') }} đ</span>
                                        </td>
                                        <td style="width: 140px;" class="text-center">
                                            <div class="input-group input-group-sm justify-content-center">
                                                <input type="number" class="form-control qty-input-field update-cart" 
                                                       data-id="{{ $id }}" value="{{ $qty }}" min="1">
                                            </div>
                                        </td>
                                        <td class="text-end fw-bold text-dark">
                                            <span class="subtotal-item">{{ number_format($itemTotal, 0, ',', '.') }} đ</span>
                                        </td>
                                        <td class="text-center pe-4">
                                            <form action="{{ route('cart.remove') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="product_id" value="{{ $id }}">
                                                <button type="submit" class="btn-delete-custom border-0 shadow-sm" onclick="return confirm('Xóa sản phẩm này khỏi giỏ hàng?')">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="cart-summary shadow-sm">
                        <h5 class="fw-bold mb-4 pb-2 border-bottom">Tóm tắt đơn hàng</h5>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-secondary">Tạm tính</span>
                            <span class="fw-bold text-dark total-cart">{{ number_format($total, 0, ',', '.') }} đ</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-secondary">Phí vận chuyển</span>
                            <span class="text-success fw-bold">Miễn phí</span>
                        </div>
                        <hr class="my-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <span class="h5 fw-bold mb-0">Tổng cộng</span>
                            <span class="h4 text-success fw-bold mb-0 total-cart">{{ number_format($total, 0, ',', '.') }} đ</span>
                        </div>
                        <a href="{{ route('checkout') }}" class="btn btn-success btn-lg w-100 py-3 fw-bold rounded-3">
                            TIẾN HÀNH THANH TOÁN
                        </a>
                        <div class="mt-3 text-center">
                            <small class="text-muted"><i class="fas fa-shield-alt me-1"></i> Thanh toán an toàn & bảo mật</small>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Lắng nghe sự kiện thay đổi số lượng bằng Ajax
        $('.update-cart').on('change', function() {
            let quantity = $(this).val();
            let id = $(this).data('id');
            let row = $(this).closest('tr');

            if(quantity < 1) {
                $(this).val(1);
                return;
            }

            $.ajax({
                url: '{{ route("cart.update") }}',
                method: "POST",
                data: {
                    _token: '{{ csrf_token() }}',
                    product_id: id,
                    quantity: quantity
                },
                success: function (response) {
                    if(response.success) {
                        // Cập nhật thành tiền từng dòng
                        row.find('.subtotal-item').text(response.newSubtotal);
                        // Cập nhật tổng tiền toàn giỏ hàng
                        $('.total-cart').text(response.newTotal);
                    }
                },
                error: function(xhr) {
                    alert('Không thể cập nhật giỏ hàng. Vui lòng thử lại!');
                    console.log(xhr.responseText);
                }
            });
        });
    });
</script>
@endpush
@endsection