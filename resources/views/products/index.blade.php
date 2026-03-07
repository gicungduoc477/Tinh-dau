@extends('layouts.app')

@section('title', 'Cửa hàng Tinh dầu Thiên nhiên - Tinh túy từ đất mẹ')

@section('content')
@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&family=Playfair+Display:ital@0;1&family=WindSong:wght@400;500&display=swap" rel="stylesheet" />

<style>
    :root {
        --primary-color: #27ae60;
        --primary-light: rgba(39, 174, 96, 0.1);
        --price-color: #e74c3c;
        --card-radius: 20px;
        --pure-oil: #27ae60;
        --blend-oil: #2980b9;
        --fragrance: #f39c12;
        --puddle-deep: #b3dec6; 
    }

    body { background-color: #f8f9fa; font-family: 'Inter', sans-serif; overflow-x: hidden; }

    .carousel-control-prev, .carousel-control-next {
        width: 5%;
        opacity: 0;
        transition: all 0.4s ease;
    }

    .gg-italic { font-family: 'Playfair Display', serif; }

    .hero-slider:hover .carousel-control-prev, 
    .hero-slider:hover .carousel-control-next { opacity: 1; }

    .control-icon-wrapper {
        width: 50px; height: 50px;
        background-color: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(5px);
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        color: white; font-size: 1.2rem;
        transition: all 0.3s ease;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .carousel-control-prev:hover .control-icon-wrapper,
    .carousel-control-next:hover .control-icon-wrapper {
        background-color: var(--primary-color);
        box-shadow: 0 0 15px rgba(39, 174, 96, 0.5);
    }

    /* --- 1. Hero Slider --- */
    .hero-slider { margin: 20px 0 50px; border-radius: 30px; overflow: hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
    .carousel-item { height: 450px; position: relative; }
    .carousel-item img { object-fit: cover; height: 100%; width: 100%; filter: brightness(0.7); }
    .carousel-caption { text-align: left; bottom: 20%; left: 10%; max-width: 600px; animation: fadeInUp 1s ease; }
    .carousel-caption h2 { font-size: 3.5rem; font-weight: 800; margin-bottom: 15px; text-shadow: 2px 2px 10px rgba(0,0,0,0.3); }
    .btn-cta { padding: 12px 35px; border-radius: 50px; font-weight: 700; text-transform: uppercase; transition: 0.3s; }
    .btn-cta-primary { background: var(--primary-color); border: none; color: white; }
    .btn-cta-outline { background: transparent; border: 2px solid white; color: white; margin-left: 10px; }

    /* --- 2. Welcome Section --- */
    .welcom { position: relative; background: #ffffff; border-radius: 40px; margin: 40px 0; padding: 80px 0; overflow: hidden; z-index: 1; }
    .special-font-title { font-family: 'WindSong', cursive; font-size: 4rem; color: var(--primary-color); }
    .puddle-bg {
        position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
        width: 110%; height: 120%;
        background: radial-gradient(circle at 70% 30%, #b3dec6 0%, #d4ede0 40%, #ffffff 85%);
        border-radius: 43% 57% 38% 62% / 54% 41% 59% 46%;
        z-index: -1; opacity: 0.7; animation: water-flow 15s infinite alternate ease-in-out;
    }
    .rain-container { position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: 0; }
    .drop {
        position: absolute; width: 25px; height: 35px;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 42'%3E%3Cpath d='M15 3Q15 3 15 3C15 3 27 18 27 28A12 12 0 0 1 3 28C3 18 15 3 15 3Z' fill='%2327ae60' opacity='0.8'/%3E%3C/svg%3E");
        background-size: contain; background-repeat: no-repeat; animation: fall linear forwards;
    }
    .ripple { position: absolute; border: 2px solid var(--primary-color); border-radius: 50%; transform: scale(0); animation: ripple-effect 0.8s ease-out; opacity: 0.6; }

    @keyframes fall {
        0% { transform: translateY(-100px) scale(0.8); opacity: 0; }
        10% { opacity: 1; }
        100% { transform: translateY(600px) scale(1.2); opacity: 0; }
    }
    @keyframes ripple-effect { 0% { transform: scale(0); opacity: 0.6; } 100% { transform: scale(4); opacity: 0; } }
    @keyframes water-flow {
        0% { border-radius: 43% 57% 38% 62%; transform: translate(-50%, -50%) rotate(0deg); }
        100% { border-radius: 45% 55% 40% 60%; transform: translate(-51%, -49%) rotate(-1deg); }
    }

    /* --- 3. Search & Sidebar --- */
    .search-wrapper { max-width: 800px; margin: 0 auto 4rem; }
    .search-bar { border-radius: 50px; padding: 10px; background: #fff; box-shadow: 0 15px 35px rgba(0,0,0,0.05); display: flex; border: 1px solid #edf2f7; }
    .search-bar input { border: none; box-shadow: none !important; padding-left: 20px; }
    .search-bar button { border-radius: 50px; padding: 12px 35px; background: var(--primary-color); border: none; color: white; font-weight: 700; }

    .category-sidebar { background: #fff; border-radius: var(--card-radius); padding: 1.8rem; border: 1px solid #f1f5f9; position: sticky; top: 100px; }
    .class-filter-item { 
        display: flex; align-items: center; padding: 12px 15px; border-radius: 15px; 
        text-decoration: none; color: #4a5568; transition: all 0.2s; margin-bottom: 10px; border: 1px solid transparent; 
        cursor: pointer;
    }
    .class-filter-item:hover { background: #f8fafc; color: var(--primary-color); transform: translateX(5px); }
    .class-filter-item.active { background: var(--primary-light); color: var(--primary-color); font-weight: 700; border-color: rgba(39, 174, 96, 0.2); }
    .icon-box { width: 35px; height: 35px; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-right: 12px; }

    /* --- 4. Product Cards --- */
    .product-card { border: none; border-radius: var(--card-radius); background: #fff; transition: 0.4s; overflow: hidden; }
    .product-card:hover { transform: translateY(-10px); box-shadow: 0 20px 40px rgba(0,0,0,0.08); }
    .img-wrapper { position: relative; aspect-ratio: 1/1; overflow: hidden; margin: 15px; border-radius: 15px; background: #fbfbfb; }
    .product-card img { width: 100%; height: 100%; object-fit: contain; padding: 15px; transition: 0.8s; }
    .classification-tag { position: absolute; top: 15px; left: 15px; z-index: 10; font-size: 0.65rem; font-weight: 800; padding: 5px 12px; border-radius: 50px; color: #fff; }
    .badge-pure { background-color: var(--pure-oil); }
    .badge-blend { background-color: var(--blend-oil); }
    .badge-fragrance { background-color: var(--fragrance); }
    .product-title { font-size: 1.05rem; font-weight: 700; height: 2.8em; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }
    .product-title a { text-decoration: none; color: #2d3748; }
    .price { font-size: 1.25rem; font-weight: 800; color: var(--price-color); }

    /* Loading Overlay */
    #product-data-container { position: relative; min-height: 400px; }
    .loading-overlay { 
        position: absolute; top: 0; left: 0; width: 100%; height: 100%; 
        background: rgba(255,255,255,0.7); z-index: 100; display: none;
        align-items: center; justify-content: center;
    }

    /* --- 5. Owl Carousel --- */
    .owl-carousel .item img { border-radius: 20px; height: 200px; object-fit: cover; }
    .owl-carousel .item .img-container { overflow: hidden; border-radius: 15px; }

    @keyframes fadeInUp { from { opacity: 0; transform: translateY(40px); } to { opacity: 1; transform: translateY(0); } }
</style>
@endpush

<div class="container">
    {{-- Hero Slider --}}
    <div id="mainBanner" class="carousel slide hero-slider" data-bs-ride="carousel">
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#mainBanner" data-bs-slide-to="0" class="active"></button>
            <button type="button" data-bs-target="#mainBanner" data-bs-slide-to="1"></button>
        </div>
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="https://images.unsplash.com/photo-1602928321679-560bb453f190?q=80&w=2000" alt="Pure Essential Oil">
                <div class="carousel-caption">
                    <h2>Tinh Dầu Nguyên Chất</h2>
                    <p>Chiết xuất 100% từ thảo mộc thiên nhiên, mang lại sự thanh khiết cho tâm hồn.</p>
                    <a href="{{ route('products.index', ['class' => 'Tinh dầu nguyên chất']) }}" class="btn btn-cta btn-cta-primary">Mua ngay</a>
                    <a href="#product-list" class="btn btn-cta btn-cta-outline">Xem bộ sưu tập</a>
                </div>
            </div>
            <div class="carousel-item">
                <img src="https://images.unsplash.com/photo-1540324155974-7523202daa3f?q=80&w=2000" alt="Aroma Blend">
                <div class="carousel-caption">
                    <h2>Hương Trị Liệu Blend</h2>
                    <p>Sự kết hợp hoàn hảo giúp bạn thư giãn, giảm stress và ngủ ngon hơn.</p>
                    <a href="{{ route('products.index', ['class' => 'Tinh dầu hỗn hợp (Blend Oil)']) }}" class="btn btn-cta btn-cta-primary">Khám phá ngay</a>
                </div>
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#mainBanner" data-bs-slide="prev">
            <div class="control-icon-wrapper"><i class="fa-solid fa-chevron-left"></i></div>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#mainBanner" data-bs-slide="next">
            <div class="control-icon-wrapper"><i class="fa-solid fa-chevron-right"></i></div>
        </button>
    </div>
</div>

{{-- Welcome Section --}}
<div class="container-fluid welcom wow animate__animated animate__fadeInUp">
    <div class="rain-container" id="rainContainer"></div>
    <div class="puddle-bg"></div> 
    <div class="container-xl py-5 position-relative" style="z-index: 10;">
        <div class="row align-items-center">
            <div class="col-md-6 wow animate__animated animate__slideInLeft" data-wow-delay="0.2s">
                <h1 class="fw-bold text-dark mb-4">
                    Chào mừng đến với <br>
                    <span class="special-font-title">Thế Giới Tinh Dầu</span>
                </h1>
                <p class="lead text-muted fst-italic gg-italic">
                    Trải nghiệm những dịch vụ tuyệt vời, chất lượng tinh dầu hàng đầu được chiết xuất hoàn toàn tự nhiên.
                </p>
                <div class="d-flex gap-4 mt-4">
                    <div class="text-center">
                        <div class="mb-1"><i class="bi bi-patch-check-fill text-success fs-4"></i></div>
                        <h4 class="fw-bold text-success mb-0">100%</h4>
                        <small class="text-uppercase fw-bold text-muted" style="font-size: 0.7rem;">Tự nhiên</small>
                    </div>
                    <div class="vr opacity-10"></div>
                    <div class="text-center">
                        <div class="mb-1"><i class="bi bi-flower1 text-success fs-4"></i></div>
                        <h4 class="fw-bold text-success mb-0">50+</h4>
                        <small class="text-uppercase fw-bold text-muted" style="font-size: 0.7rem;">Mùi hương</small>
                    </div>
                    <div class="vr opacity-10"></div>
                    <div class="text-center">
                        <div class="mb-1"><i class="bi bi-headset text-success fs-4"></i></div>
                        <h4 class="fw-bold text-success mb-0">24/7</h4>
                        <small class="text-uppercase fw-bold text-muted" style="font-size: 0.7rem;">Hỗ trợ</small>
                    </div>
                </div>
                <button class="btn btn-success btn-lg px-5 rounded-pill mt-5 shadow-sm d-inline-flex align-items-center gap-2">
                    Tìm hiểu thêm <i class="bi bi-arrow-right-circle"></i>
                </button>
            </div>
            <div class="col-md-6 text-center mt-5 mt-md-0 wow animate__animated animate__zoomIn" data-wow-delay="0.4s">
                <div class="position-relative">
                    <img src="https://images.unsplash.com/photo-1608571423902-eed4a5ad8108?q=80&w=800" class="img-fluid rounded-4 shadow-lg welcome-img" alt="Tinh dầu">
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Product List Section --}}
<div class="container py-2" id="product-list">
    <div class="search-wrapper wow animate__animated animate__fadeInDown">
        <div class="text-center mb-4">
            <h2 class="fw-bold">Tìm Kiếm Mùi Hương Của Bạn</h2>
        </div>
        <form action="{{ route('products.search') }}" method="GET" class="search-bar">
            <input type="text" name="q" class="form-control" placeholder="Ví dụ: Tinh dầu Bạc Hà, Sả Chanh..." value="{{ request('q') }}">
            <button type="submit" class="btn shadow-sm">Tìm kiếm ngay</button>
        </form>
    </div>

    <div class="row g-4">
        {{-- Sidebar --}}
        <div class="col-lg-3">
            <div class="category-sidebar shadow-sm">
                <h6 class="text-uppercase fw-bold mb-4" style="letter-spacing: 1.5px; font-size: 0.75rem; color: #a0aec0;">Phân loại tinh dầu</h6>
                <div class="class-filters">
                    <a href="javascript:void(0)" data-url="{{ route('products.index', ['class' => 'Tinh dầu nguyên chất']) }}" class="class-filter-item filter-ajax {{ request('class') == 'Tinh dầu nguyên chất' ? 'active' : '' }}">
                        <div class="icon-box badge-pure text-white"><i class="fa-solid fa-leaf"></i></div>
                        <span class="small">Nguyên chất 100%</span>
                    </a>
                    <a href="javascript:void(0)" data-url="{{ route('products.index', ['class' => 'Tinh dầu hỗn hợp (Blend Oil)']) }}" class="class-filter-item filter-ajax {{ request('class') == 'Tinh dầu hỗn hợp (Blend Oil)' ? 'active' : '' }}">
                        <div class="icon-box badge-blend text-white"><i class="fa-solid fa-droplet"></i></div>
                        <span class="small">Hỗn hợp (Blend)</span>
                    </a>
                    <a href="javascript:void(0)" data-url="{{ route('products.index', ['class' => 'Tinh dầu không nguyên chất']) }}" class="class-filter-item filter-ajax {{ request('class') == 'Tinh dầu không nguyên chất' ? 'active' : '' }}">
                        <div class="icon-box badge-fragrance text-white"><i class="fa-solid fa-wind"></i></div>
                        <span class="small">Hương liệu pha</span>
                    </a>
                </div>
                <hr class="my-4" style="opacity: 0.1;">
                <h6 class="text-uppercase fw-bold mb-3" style="letter-spacing: 1.5px; font-size: 0.75rem; color: #a0aec0;">Theo danh mục</h6>
                <div class="list-group category-list">
                    <a href="javascript:void(0)" data-url="{{ route('products.index') }}" class="list-group-item list-group-item-action border-0 rounded-3 mb-1 filter-ajax {{ !request('category') && !request('class') ? 'active' : '' }}">Tất cả sản phẩm</a>
                    @foreach($categories as $cat)
                        <a href="javascript:void(0)" data-url="{{ route('products.index', ['category' => $cat->slug]) }}" class="list-group-item list-group-item-action border-0 rounded-3 mb-1 filter-ajax {{ request('category') == $cat->slug ? 'active' : '' }}">
                            {{ $cat->name }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Product Display Area --}}
        <div class="col-lg-9" id="product-data-container">
            <div class="loading-overlay" id="loader"><div class="spinner-border text-success"></div></div>
            
            <div id="product-data">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0 fw-bold">Kết quả: <span class="text-primary">{{ $products->total() }}</span> sản phẩm</h5>
                </div>

                <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
                    @forelse($products as $product)
                    <div class="col wow animate__animated animate__fadeInUp" data-wow-delay="0.1s">
                        <div class="card product-card h-100 shadow-sm">
                            <span class="classification-tag {{ $product->classification == 'Tinh dầu nguyên chất' ? 'badge-pure' : ($product->classification == 'Tinh dầu hỗn hợp (Blend Oil)' ? 'badge-blend' : 'badge-fragrance') }}">
                                {{ $product->classification == 'Tinh dầu nguyên chất' ? 'Pure Oil' : ($product->classification == 'Tinh dầu hỗn hợp (Blend Oil)' ? 'Aroma Blend' : 'Fragrance') }}
                            </span>
                            <div class="img-wrapper">
                                <a href="{{ route('products.show', $product->slug) }}">
                                    @php $imagePath = 'uploads/product/' . $product->image; @endphp
                                    <img src="{{ (!empty($product->image) && file_exists(public_path($imagePath))) ? asset($imagePath) : asset('backend/img/no-image.png') }}" alt="{{ $product->name }}">
                                </a>
                            </div>
                            <div class="card-body p-4 pt-2">
                                <div class="mb-1"><span class="text-primary fw-bold small text-uppercase">{{ $product->category->name ?? 'Natural' }}</span></div>
                                <h5 class="product-title"><a href="{{ route('products.show', $product->slug) }}">{{ $product->name }}</a></h5>
                                <div class="d-flex justify-content-between align-items-end">
                                    <span class="price">{{ number_format($product->price,0,',','.') }} ₫</span>
                                    <div class="text-success small fw-bold"><i class="fa-solid fa-circle-check me-1"></i>Sẵn hàng</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-12 text-center py-5 shadow-sm bg-white rounded-4 w-100">
                        <i class="fa-solid fa-magnifying-glass text-muted fs-1"></i>
                        <h4 class="text-muted mt-4">Không tìm thấy sản phẩm nào!</h4>
                    </div>
                    @endforelse
                </div>

                <div class="mt-5 d-flex justify-content-center ajax-pagination">
                    {{ $products->links() }}
                </div>
            </div>

            {{-- Best Sellers --}}
            <div class="mt-5 pt-5 wow animate__animated animate__slideInUp">
                <hr class="mb-5 opacity-10">
                <h3 class="text-center mb-4 fw-bold text-uppercase text-danger"><i class="fa-solid fa-fire-flame-curved me-2"></i>Top bán chạy</h3>
                <div class="owl-carousel owl-theme">
                    @php 
                        $demo_images = ['https://images.unsplash.com/photo-1608571423902-eed4a5ad8108','https://images.unsplash.com/photo-1540324155974-7523202daa3f','https://images.unsplash.com/photo-1611080626919-7cf5a9dbab5b','https://images.unsplash.com/photo-1595981234058-a9302fb97229','https://images.unsplash.com/photo-1515377905703-c4788e51af15'];
                        $names = ['Oải Hương', 'Sả Chanh', 'Bạc Hà', 'Tràm Trà', 'Quế'];
                    @endphp
                    @foreach($demo_images as $index => $img)
                    <div class="item text-center">
                        <div class="img-container shadow-sm mb-3"><img src="{{ $img }}?q=80&w=600" alt="Tinh dầu"></div>
                        <p class="mt-2 small fw-bold">Tinh dầu {{ $names[$index] }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/wow/1.1.2/wow.min.js"></script>

<script>
    new WOW().init();

    $(document).ready(function(){
        // Khởi tạo Carousel
        $('.owl-carousel').owlCarousel({ 
            loop:true, margin:15, nav:false, autoplay:true, 
            autoplayTimeout:3000, autoplayHoverPause:true, 
            responsive:{ 0:{ items:1 }, 600:{ items:3 }, 1000:{ items:5 } } 
        });

        // --- XỬ LÝ AJAX LỌC SẢN PHẨM ---
        $(document).on('click', '.filter-ajax, .ajax-pagination a', function(e) {
            e.preventDefault();
            
            let url = $(this).data('url') || $(this).attr('href');
            if(!url || url === 'javascript:void(0)') return;

            // Hiệu ứng loading
            $('#loader').css('display', 'flex');
            $('#product-data').css('opacity', '0.5');

            $.ajax({
                url: url,
                type: 'GET',
                success: function(response) {
                    // Cập nhật vùng chứa sản phẩm
                    let html = $(response).find('#product-data').html();
                    $('#product-data').html(html);
                    
                    // Cập nhật URL thanh địa chỉ
                    window.history.pushState({path: url}, '', url);
                    
                    // Cập nhật trạng thái Active sidebar
                    updateActiveState(url);

                    // Khởi tạo lại WOW cho các item mới
                    new WOW().init();

                    // Tắt loading
                    $('#loader').hide();
                    $('#product-data').css('opacity', '1');
                },
                error: function() {
                    $('#loader').hide();
                    $('#product-data').css('opacity', '1');
                }
            });
        });

        function updateActiveState(url) {
            $('.filter-ajax').removeClass('active');
            $('.filter-ajax').each(function() {
                if($(this).data('url') === url) $(this).addClass('active');
            });
        }

        // --- HIỆU ỨNG MƯA RƠI ---
        function createDrop() {
            const container = document.getElementById('rainContainer');
            if(!container) return;
            const drop = document.createElement('div');
            drop.classList.add('drop');
            const leftPos = 10 + (Math.random() * 80);
            const duration = 2 + (Math.random() * 2); 
            drop.style.left = leftPos + '%';
            drop.style.animationDuration = duration + 's';
            container.appendChild(drop);
            setTimeout(() => {
                createRipple(leftPos);
                drop.remove();
            }, duration * 1000);
        }

        function createRipple(left) {
            const container = document.getElementById('rainContainer');
            const ripple = document.createElement('div');
            ripple.classList.add('ripple');
            ripple.style.left = `calc(${left}% - 5px)`;
            ripple.style.bottom = '10%'; 
            ripple.style.width = '20px'; ripple.style.height = '10px';
            container.appendChild(ripple);
            setTimeout(() => { ripple.remove(); }, 800);
        }
        setInterval(createDrop, 600);
    });
</script>
@endpush
@endsection