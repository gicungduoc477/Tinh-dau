@extends('layouts.app')

@section('title', 'Giỏ hàng của bạn')

@section('content')
<div class="container mt-3 mt-lg-5 pb-5">
    @push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --primary-color: #28a745; --bg-body: #f8f9fa; --danger-color: #dc3545; }
        body { background-color: var(--bg-body); }
        
        /* Card tổng thể */
        .cart-card { border-radius: 20px; border: none; box-shadow: 0 8px 25px rgba(0,0,0,0.05); background: #fff; overflow: hidden; }
        .cart-item { border-bottom: 1px solid #f1f1f1; transition: 0.3s; position: relative; }
        .cart-item:last-child { border-bottom: none; }
        .cart-item:hover { background-color: #fcfcfc; }

        /* Hình ảnh sản phẩm */
        .product-img-wrap { width: 90px; height: 90px; flex-shrink: 0; }
        .cart-product-image { width: 100%; height: 100%; object-fit: cover; border-radius: 15px; border: 1px solid #f0f0f0; }

        /* Bộ tăng giảm số lượng mini */
        .quantity-group { 
            display: flex; align-items: center; background: #f1f3f5; 
            border-radius: 10px; padding: 2px; width: fit-content;
        }
        .qty-btn { 
            border: none; background: transparent; width: 30px; height: 30px; 
            display: flex; align-items: center; justify-content: center; 
            color: #495057; font-size: 0.85rem; transition: 0.2s;
        }
        .qty-btn:hover { color: var(--primary-color); }
        .qty-input-field { 
            width: 35px; border: none; background: transparent; 
            text-align: center; font-weight: 700; font-size: 0.95rem; 
        }
        .qty-input-field::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }

        /* Nút xóa */
        .btn-remove-item { 
            width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; 
            border-radius: 50%; background: #fff5f5; color: var(--danger-color); 
            border: 1px solid #feb2b2; cursor: pointer; transition: 0.2s;
        }
        .btn-remove-item:hover { background: var(--danger-color); color: #fff; transform: scale(1.1); }

        /* Summary Sticky Mobile */
        .cart-summary { 
            border-radius: 20px; background: #fff; padding: 24px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.06); border: 1px solid #f0f0f0;
        }

        @media (max-width: 991.98px) {
            .product-img-wrap { width: 80px; height: 80px; }
            .cart-summary-fixed { 
                position: fixed; bottom: 0; left: 0; right: 0; z-index: 1050; 
                background: rgba(255, 255, 255, 0.92);
                backdrop-filter: blur(15px);
                border-radius: 25px 25px 0 0; 
                padding: 15px 20px 25px 20px;
                box-shadow: 0 -8px 25px rgba(0,0,0,0.1);
                border-top: 1px solid rgba(0,0,0,0.05);
            }
            .container { padding-bottom: 160px !important; } /* Tránh bị che bởi thanh thanh toán */
            .product-name { font-size: 0.95rem; font-weight: 700; color: #2d3436; }
        }
    </style>
    @endpush

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4 px-1">
        <div>
            <h3 class="fw-bold mb-0">Giỏ hàng</h3>
            <span class="text-muted small">Bạn có {{ count($cart ?? []) }} món trong danh sách</span>
        </div>
        <a href="{{ route('products.index') }}" class="btn btn-outline-success border-2 rounded-pill px-3 fw-bold btn-sm">
            <i class="fas fa-plus me-1"></i> Thêm món
        </a>
    </div>

    @if(empty($cart) || count($cart) == 0)
        <div class="card cart-card p-5 text-center">
            <div class="py-4">
                <i class="fas fa-shopping-bag fa-4x text-light mb-3"></i>
                <h5 class="fw-bold text-secondary">Giỏ hàng của bạn đang trống</h5>
                <p class="text-muted small px-4">Hãy chọn cho mình những món ngon nhất từ thực đơn của chúng tôi!</p>
                <a href="{{ route('products.index') }}" class="btn btn-success mt-3 rounded-pill px-4 fw-bold">XEM THỰC ĐƠN NGAY</a>
            </div>
        </div>
    @else
        <div class="row g-4">
            {{-- Danh sách sản phẩm --}}
            <div class="col-lg-8">
                <div class="card cart-card">
                    <div class="card-body p-0">
                        @foreach($cart as $id => $item)
                        @php 
                            $price = (float)($item['price'] ?? 0);
                            $qty = (int)($item['quantity'] ?? 1);
                            $imgName = trim($item['image'] ?? '');
                            $finalUrl = filter_var($imgName, FILTER_VALIDATE_URL) ? $imgName : 
                                       (!empty($imgName) ? (str_contains($imgName, '/') ? asset(ltrim($imgName, '/')) : asset('uploads/product/' . $imgName)) : 
                                       asset('backend/img/no-image.png'));
                        @endphp
                        
                        <div class="cart-item p-3 p-lg-4" data-id="{{ $id }}">
                            <div class="d-flex align-items-start align-items-lg-center">
                                {{-- Thumbnail --}}
                                <div class="product-img-wrap">
                                    <img src="{{ $finalUrl }}" class="cart-product-image" onerror="this.src='https://placehold.co/150x150?text=Food'">
                                </div>

                                {{-- Details --}}
                                <div class="ms-3 flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="product-name text-truncate mb-1" style="max-width: 200px;">{{ $item['name'] ?? 'Sản phẩm' }}</div>
                                        <form action="{{ route('cart.remove') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="product_id" value="{{ $id }}">
                                            <button type="submit" class="btn-remove-item" onclick="return confirm('Xóa món này khỏi giỏ?')">
                                                <i class="fas fa-times small"></i>
                                            </button>
                                        </form>
                                    </div>
                                    
                                    <div class="d-flex flex-column flex-lg-row justify-content-lg-between align-items-lg-center mt-2 mt-lg-0">
                                        <div class="fw-bold text-success h6 mb-2 mb-lg-0">{{ number_format($price, 0, ',', '.') }} đ</div>
                                        
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="quantity-group me-lg-4">
                                                <button type="button" class="qty-btn btn-minus"><i class="fas fa-minus"></i></button>
                                                <input type="number" class="qty-input-field update-cart" data-id="{{ $id }}" value="{{ $qty }}" min="1">
                                                <button type="button" class="qty-btn btn-plus"><i class="fas fa-plus"></i></button>
                                            </div>
                                            <div class="fw-bold text-dark subtotal-item d-none d-lg-block">
                                                {{ number_format($price * $qty, 0, ',', '.') }} đ
                                            </div>
                                        </div>
                                    </div>
                                    {{-- Giá phụ cho mobile --}}
                                    <div class="d-lg-none text-end mt-1">
                                        <span class="small text-muted">Thành tiền: </span>
                                        <span class="fw-bold text-dark subtotal-item">{{ number_format($price * $qty, 0, ',', '.') }} đ</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Checkout Sidebar --}}
            <div class="col-lg-4">
                <div class="cart-summary cart-summary-fixed">
                    <h5 class="fw-bold mb-4 d-none d-lg-block">Chi tiết thanh toán</h5>
                    
                    <div class="d-flex justify-content-between align-items-center mb-lg-3 mb-2">
                        <span class="text-muted">Tạm tính</span>
                        <span class="fw-bold total-cart">{{ number_format($total, 0, ',', '.') }} đ</span>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted small">Phí giao hàng</span>
                        <span class="text-success fw-bold small">FREE</span>
                    </div>
                    
                    <hr class="d-none d-lg-block my-3">
                    
                    <div class="d-flex justify-content-between align-items-center mb-lg-4 mb-3">
                        <span class="h6 fw-bold mb-0">TỔNG CỘNG</span>
                        <span class="h4 text-success fw-bold mb-0 total-cart">{{ number_format($total, 0, ',', '.') }} đ</span>
                    </div>

                    <a href="{{ route('checkout') }}" class="btn btn-success w-100 py-3 fw-bold rounded-pill shadow-sm text-uppercase border-0">
                        Đặt hàng ngay <i class="fas fa-chevron-right ms-2 small"></i>
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Tăng
        $('.btn-plus').on('click', function() {
            let input = $(this).siblings('.update-cart');
            input.val(parseInt(input.val()) + 1).trigger('change');
        });

        // Giảm
        $('.btn-minus').on('click', function() {
            let input = $(this).siblings('.update-cart');
            let val = parseInt(input.val());
            if(val > 1) { input.val(val - 1).trigger('change'); }
        });

        // Ajax cập nhật
        $('.update-cart').on('change', function() {
            let qty = $(this).val();
            let id = $(this).data('id');
            let row = $(this).closest('.cart-item');
            
            if(qty < 1) { $(this).val(1); return; }

            row.css('opacity', '0.6');

            $.ajax({
                url: '{{ route("cart.update") }}',
                method: "POST",
                data: {
                    _token: '{{ csrf_token() }}',
                    product_id: id,
                    quantity: qty
                },
                success: function (res) {
                    row.css('opacity', '1');
                    if(res.success) {
                        // Cập nhật thành tiền của món đó (cả trên PC và Mobile)
                        row.find('.subtotal-item').text(res.newSubtotal + ' đ');
                        // Cập nhật tổng cộng giỏ hàng
                        $('.total-cart').text(res.newTotal + ' đ');
                    }
                },
                error: function() {
                    row.css('opacity', '1');
                    alert('Lỗi cập nhật giỏ hàng!');
                }
            });
        });
    });
</script>
@endpush
@endsection