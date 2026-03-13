@extends('layouts.app')

@section('title', $product->name . ' - Tinh dầu thiên nhiên')

@section('content')
@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
    :root {
        --primary-color: #27ae60;
        --primary-dark: #219150;
        --price-color: #e74c3c;
        --text-main: #2d3748;
        --text-muted: #718096;
        --bg-light: #f8f9fa;
    }

    .product-detail-container { 
        background: #fff; border-radius: 30px; padding: 40px; 
        box-shadow: 0 10px 40px rgba(0,0,0,0.03); margin-top: 20px;
    }
    
    .product-image-wrapper { 
        background: var(--bg-light); border-radius: 25px; overflow: hidden; 
        position: relative; border: 1px solid #f1f1f1; aspect-ratio: 1 / 1; 
        display: flex; align-items: center; justify-content: center;
    }
    .product-image-wrapper img { 
        transition: transform 0.6s cubic-bezier(0.165, 0.84, 0.44, 1); 
        width: 100%; height: 100%; object-fit: contain; padding: 20px;
    }
    .product-image-wrapper:hover img { transform: scale(1.08); }

    .entry-title { font-size: 2.5rem; font-weight: 800; color: var(--text-main); margin-bottom: 10px; line-height: 1.2; }
    
    .classification-label {
        display: inline-flex; align-items: center; padding: 6px 16px; border-radius: 50px;
        font-size: 0.85rem; font-weight: 700; text-transform: uppercase; margin-bottom: 20px;
        background: #e6fffa; color: var(--primary-color); border: 1px solid #c6f6d5;
    }

    .price-tag { font-size: 2.8rem; color: var(--price-color); font-weight: 800; margin-bottom: 10px; letter-spacing: -1px; }
    
    /* Variant Styles */
    .variant-options { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
    .variant-item input { display: none; }
    .variant-item label {
        padding: 10px 22px; border: 2px solid #edf2f7; border-radius: 50px;
        cursor: pointer; font-weight: 600; transition: all 0.3s; color: var(--text-main);
        background: white;
    }
    .variant-item input:checked + label {
        border-color: var(--primary-color); background: #e6fffa; color: var(--primary-color);
        box-shadow: 0 4px 12px rgba(39, 174, 96, 0.1);
    }

    .purchase-box { 
        background: #fcfcfc; border: 1px solid #edf2f7; border-radius: 20px; 
        padding: 30px; margin-top: 20px;
    }
    
    .qty-input { 
        border-radius: 50px !important; text-align: center; font-weight: 700; 
        border: 2px solid #edf2f7; height: 55px;
    }

    .btn-buy { 
        border-radius: 50px; padding: 0 30px; font-weight: 700; text-transform: uppercase; 
        height: 55px; border: none; background: var(--primary-color); color: white; 
        transition: all 0.4s ease; width: 100%; font-size: 1rem;
        display: flex; align-items: center; justify-content: center; gap: 10px;
    }
    .btn-buy:hover:not(:disabled) { transform: translateY(-3px); background: var(--primary-dark); box-shadow: 0 10px 25px rgba(39, 174, 96, 0.3); }
    .btn-buy:disabled { background: #cbd5e0; cursor: not-allowed; }

    /* Review Filters */
    .review-filter-bar { background: #f8fafc; border-radius: 15px; padding: 20px; }
    .filter-btn {
        border: 1px solid #e2e8f0; background: white; padding: 8px 18px;
        border-radius: 50px; font-size: 0.9rem; font-weight: 600; color: var(--text-muted);
        transition: 0.3s;
    }
    .filter-btn.active { background: var(--primary-color); color: white; border-color: var(--primary-color); }
    
    /* Tabs */
    .nav-tabs { border: none; gap: 10px; margin-top: 50px; }
    .nav-tabs .nav-link { 
        border: none; border-radius: 50px; padding: 12px 28px; 
        color: var(--text-muted); font-weight: 600; background: #f1f5f9; transition: 0.3s; 
    }
    .nav-tabs .nav-link.active { background: var(--primary-color); color: white; box-shadow: 0 5px 15px rgba(39, 174, 96, 0.2); }
    
    .loading-overlay {
        position: absolute; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(255,255,255,0.8); display: none; align-items: center;
        justify-content: center; z-index: 10; border-radius: 15px;
    }

    @media (max-width: 768px) {
        .product-detail-container { padding: 20px; }
        .entry-title { font-size: 1.8rem; }
        .price-tag { font-size: 2.2rem; }
    }
</style>
@endpush

<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('products.index') }}" class="text-decoration-none text-muted"><i class="bi bi-house-door me-1"></i>Cửa hàng</a></li>
            <li class="breadcrumb-item active text-dark fw-bold" aria-current="page">{{ $product->name }}</li>
        </ol>
    </nav>

    <div class="product-detail-container">
        <div class="row g-4 g-lg-5">
            {{-- Cột Hình ảnh --}}
            <div class="col-lg-6 animate__animated animate__fadeIn">
                <div class="product-image-wrapper shadow-sm">
                    @php
                        $imgName = trim($product->image);
                        if (filter_var($imgName, FILTER_VALIDATE_URL)) {
                            $finalUrl = $imgName;
                        } elseif (!empty($imgName) && !str_contains($imgName, '/')) {
                            $finalUrl = asset('uploads/product/' . $imgName);
                        } elseif (!empty($imgName)) {
                            $finalUrl = asset(ltrim($imgName, '/'));
                        } else {
                            $finalUrl = asset('backend/img/no-image.png');
                        }
                    @endphp
                    <img id="main-product-image" src="{{ $finalUrl }}" alt="{{ $product->name }}"
                         onerror="this.onerror=null; this.src='https://placehold.co/600x600?text=San+Pham';">
                </div>
            </div>

            {{-- Cột Thông tin --}}
            <div class="col-lg-6">
                <div class="ps-lg-2">
                    <span class="classification-label">
                        <i class="bi bi-patch-check-fill me-2"></i> {{ $product->classification ?? 'Tinh dầu thiên nhiên' }}
                    </span>
                    
                    <h1 class="entry-title">{{ $product->name }}</h1>
                    
                    <div class="d-flex align-items-center mb-4">
                        <div class="text-warning me-2">
                            @php $avg = $reviews->avg('rating') ?? 0; @endphp
                            @for($i = 1; $i <= 5; $i++)
                                <i class="bi bi-star{{ $i <= $avg ? '-fill' : ($i - 0.5 <= $avg ? '-half' : '') }}"></i>
                            @endfor
                        </div>
                        <span class="text-muted small fw-medium">({{ $ratingCounts['all'] ?? 0 }} đánh giá)</span>
                    </div>

                    <div class="price-tag"><span id="display-price">{{ number_format($product->price, 0, ',', '.') }}</span> ₫</div>
                    
                    <div class="features-list mb-4">
                        <p class="text-success small mb-2 fw-bold"><i class="bi bi-check-circle-fill me-2"></i><span id="stock-status">Sẵn hàng - Giao hỏa tốc 2h</span></p>
                        <p class="text-muted small mb-0"><i class="bi bi-shield-lock-fill me-2"></i>Bảo hành chính hãng & Đổi trả trong 7 ngày</p>
                    </div>

                    <form id="add-to-cart-form" action="{{ route('cart.add') }}" method="POST">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        
                        @if($product->variants && $product->variants->count() > 0)
                        <div class="mb-4">
                            <label class="small fw-bold text-uppercase text-muted mb-2 d-block">Chọn dung tích / Phân loại</label>
                            <div class="variant-options">
                                @foreach($product->variants as $index => $variant)
                                    <div class="variant-item">
                                        <input type="radio" name="variant_id" id="variant-{{ $variant->id }}" 
                                               value="{{ $variant->id }}" 
                                               data-price="{{ number_format($variant->price, 0, ',', '.') }}" 
                                               data-stock="{{ $variant->stock }}"
                                               {{ $index == 0 ? 'checked' : '' }} class="variant-input">
                                        <label for="variant-{{ $variant->id }}">{{ $variant->name }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <div class="purchase-box shadow-sm">
                            <div class="row g-3 align-items-end">
                                <div class="col-4 col-md-3">
                                    <label class="qty-label small fw-bold text-uppercase text-muted mb-2 d-block">Số lượng</label>
                                    <input type="number" name="quantity" value="1" min="1" class="form-control qty-input shadow-none">
                                </div>
                                <div class="col-8 col-md-9">
                                    <button type="submit" id="btn-submit-cart" class="btn btn-buy shadow-sm">
                                        <i class="bi bi-cart-plus-fill"></i> Thêm vào giỏ hàng
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Tabs Section --}}
        <ul class="nav nav-tabs justify-content-center justify-content-lg-start" id="productTab" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#desc" type="button">Mô tả sản phẩm</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#usage" type="button">Hướng dẫn sử dụng</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#reviews-tab-content" type="button">Đánh giá khách hàng</button>
            </li>
        </ul>
        
        <div class="tab-content border-top mt-2">
            <div class="tab-pane fade show active animate__animated animate__fadeIn" id="desc">
                <div class="row py-5">
                    <div class="col-lg-12">
                        <div class="lh-lg text-secondary" style="text-align: justify">
                            {!! nl2br(e($product->description)) !!}
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="tab-pane fade animate__animated animate__fadeIn" id="usage">
                <div class="usage-card mt-4 p-5 bg-light rounded-5 border-0">
                    <h5 class="fw-bold mb-4 text-success"><i class="bi bi-stars me-2"></i>Mẹo sử dụng tinh dầu đạt hiệu quả cao nhất:</h5>
                    <div class="row g-4">
                        <div class="col-md-6 col-lg-4">
                            <div class="p-3 bg-white rounded-4 shadow-sm h-100">
                                <h6 class="fw-bold text-primary mb-3"><i class="bi bi-droplet-fill me-2"></i>Khuếch tán hương</h6>
                                <p class="small text-muted mb-0">Nhỏ 3-5 giọt vào máy xông hoặc đèn đốt. Phù hợp cho diện tích 15-20m2.</p>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <div class="p-3 bg-white rounded-4 shadow-sm h-100">
                                <h6 class="fw-bold text-success mb-3"><i class="bi bi-flower1 me-2"></i>Massage trị liệu</h6>
                                <p class="small text-muted mb-0">Pha loãng với dầu nền tỉ lệ 1:10 (1 giọt tinh dầu : 10ml dầu dừa) để massage cơ thể.</p>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <div class="p-3 bg-white rounded-4 shadow-sm h-100">
                                <h6 class="fw-bold text-info mb-3"><i class="bi bi-water me-2"></i>Tắm thư giãn</h6>
                                <p class="small text-muted mb-0">Nhỏ 5-10 giọt vào bồn tắm nước ấm và ngâm mình trong 15-20 phút để giảm stress.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade animate__animated animate__fadeIn" id="reviews-tab-content">
                <div class="row py-4 justify-content-center">
                    <div class="col-lg-11">
                        <div class="review-filter-bar mb-4 shadow-sm border bg-white">
                            <div class="d-flex flex-wrap gap-2 align-items-center">
                                <span class="fw-bold me-2 small text-uppercase text-muted">Lọc đánh giá:</span>
                                <button class="filter-btn active" data-rating="" data-image="false">Tất cả</button>
                                <button class="filter-btn" data-rating="" data-image="true"><i class="bi bi-camera me-1"></i> Có hình ảnh</button>
                                @foreach([5, 4, 3, 2, 1] as $star)
                                    <button class="filter-btn" data-rating="{{ $star }}" data-image="false">{{ $star }} <i class="bi bi-star-fill text-warning ms-1" style="font-size: 0.7rem"></i></button>
                                @endforeach
                            </div>
                        </div>

                        <div id="reviews-container" class="position-relative">
                            <div class="loading-overlay"><div class="spinner-border text-success"></div></div>
                            <div id="review-list-wrapper">
                                {{-- Phần này sẽ được Load qua AJAX --}}
                                <div class="text-center py-5">
                                    <div class="spinner-border text-success mb-2"></div>
                                    <p class="text-muted">Đang tải phản hồi khách hàng...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // --- 1. XỬ LÝ PHÂN LOẠI (VARIANTS) ---
        $('.variant-input').on('change', function() {
            const price = $(this).data('price');
            const stock = parseInt($(this).data('stock'));

            $('#display-price').fadeOut(100, function() {
                $(this).text(price).fadeIn(100);
            });

            if (stock > 0) {
                $('#stock-status').text('Sẵn hàng - Giao hỏa tốc 2h').parent().removeClass('text-danger').addClass('text-success');
                $('#btn-submit-cart').prop('disabled', false).html('<i class="bi bi-cart-plus-fill"></i> Thêm vào giỏ hàng');
            } else {
                $('#stock-status').text('Tạm hết hàng').parent().removeClass('text-success').addClass('text-danger');
                $('#btn-submit-cart').prop('disabled', true).html('Hết hàng tạm thời');
            }
        });

        // Kích hoạt variant mặc định
        $('.variant-input:checked').trigger('change');

        // --- 2. XỬ LÝ ĐÁNH GIÁ (AJAX) ---
        function fetchReviews(page = 1) {
            const activeBtn = $('.filter-btn.active');
            const rating = activeBtn.data('rating');
            const hasImage = activeBtn.data('image');

            $('.loading-overlay').css('display', 'flex');
            
            $.ajax({
                url: "{{ route('products.fetch_reviews', $product->id) }}",
                method: "GET",
                data: { page: page, rating: rating, has_image: hasImage },
                success: function(data) {
                    $('#review-list-wrapper').html(data);
                    $('.loading-overlay').hide();
                    // Scroll về đầu tab review mượt mà
                    if(page > 1) {
                        $('html, body').animate({
                            scrollTop: $("#productTab").offset().top - 100
                        }, 200);
                    }
                },
                error: function() {
                    $('#review-list-wrapper').html('<p class="text-center py-5 text-danger">Có lỗi xảy ra khi tải đánh giá. Vui lòng thử lại.</p>');
                    $('.loading-overlay').hide();
                }
            });
        }

        // Load lần đầu khi mở Tab Review
        $('button[data-bs-target="#reviews-tab-content"]').on('shown.bs.tab', function (e) {
            if($('#review-list-wrapper').find('.spinner-border').length > 0) {
                fetchReviews(1);
            }
        });

        // Click nút lọc
        $(document).on('click', '.filter-btn', function() {
            $('.filter-btn').removeClass('active');
            $(this).addClass('active');
            fetchReviews(1);
        });

        // Phân trang AJAX
        $(document).on('click', '.ajax-pagination .pagination a', function(e) {
            e.preventDefault();
            const page = $(this).attr('href').split('page=')[1];
            fetchReviews(page);
        });
    });
</script>
@endpush
@endsection