<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Tinh Dầu Nature - Tinh Túy Thiên Nhiên')</title>
    
    <link rel="icon" type="image/png" href="{{ asset('backend/img/no-image.png') }}">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,700;1,700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
    <style>
        :root {
            --primary-color: #27ae60;
            --primary-dark: #1e8449;
            --forest-green: #1a3c34;
            --nature-green: #2d5a27;
            --secondary-bg: #f8f9fa;
            --text-dark: #2d3748;
            --accent-yellow: #eab308;
        }

        body { font-family: 'Inter', sans-serif; background-color: var(--secondary-bg); color: var(--text-dark); display: flex; flex-direction: column; min-height: 100vh; }
        
        /* --- Navbar Styles --- */
        .navbar { 
            box-shadow: 0 4px 20px rgba(0,0,0,0.05); 
            background: rgba(255, 255, 255, 0.95) !important; 
            backdrop-filter: blur(10px);
            padding: 12px 0; 
            transition: all 0.3s ease;
        }
        
        .navbar-brand { 
            font-family: 'Playfair Display', serif;
            font-size: 1.7rem; 
            font-weight: 700;
            letter-spacing: -0.5px; 
            color: var(--forest-green) !important; 
            display: flex;
            align-items: center;
        }
        .navbar-brand i { 
            color: var(--primary-color); 
            font-size: 1.8rem;
            filter: drop-shadow(0 2px 4px rgba(39, 174, 96, 0.2));
        }
        .navbar-brand span { color: var(--primary-color); }

        .nav-link { 
            font-weight: 600; 
            color: var(--text-dark) !important; 
            transition: 0.3s; 
            padding: 8px 20px !important; 
            position: relative;
            font-size: 0.95rem;
        }
        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 2px;
            background: var(--primary-color);
            transition: 0.3s;
            transform: translateX(-50%);
        }
        .nav-link:hover::after { width: 60%; }
        .nav-link:hover { color: var(--primary-color) !important; }
        
        /* Cart Badge */
        .cart-badge-wrapper { position: relative; padding: 8px !important; }
        #cart-count {
            position: absolute; top: 2px; right: -2px;
            background: #ff4757; color: white;
            font-size: 0.7rem; padding: 2px 6px;
            border-radius: 50px; font-weight: 800;
            border: 2px solid #fff; 
            box-shadow: 0 2px 5px rgba(255, 71, 87, 0.3);
        }

        /* --- Footer Styles --- */
        .footer-section { margin-top: 100px; position: relative; }
        .footer-contact-bar {
            background-color: var(--nature-green);
            padding: 25px 0;
            border-radius: 60px 60px 0 0;
            position: relative;
            z-index: 10;
            box-shadow: 0 -10px 30px rgba(0,0,0,0.1);
            margin-bottom: -1px;
            color: #ffffff;
        }
        .contact-item { display: flex; align-items: center; justify-content: center; gap: 10px; transition: 0.3s; text-decoration: none; color: white; }
        .contact-item i { color: var(--accent-yellow); }
        .contact-item:hover { transform: translateY(-3px); color: var(--accent-yellow); }

        .main-footer { 
            background: linear-gradient(rgba(26, 60, 52, 0.95), rgba(13, 30, 26, 0.98)), 
                        url('https://images.unsplash.com/photo-1441974231531-c6227db76b6e?q=80&w=1920&auto=format&fit=crop'); 
            background-size: cover; background-position: center; background-attachment: fixed; 
            color: #e2e8f0; padding: 100px 0 40px;
        }

        .footer-title { 
            color: #ffffff; font-weight: 700; margin-bottom: 30px; 
            text-transform: uppercase; font-size: 1rem; letter-spacing: 2px;
            position: relative; padding-bottom: 12px;
        }
        .footer-title::after { content: ''; position: absolute; left: 0; bottom: 0; width: 45px; height: 3px; background: var(--primary-color); }

        .footer-link { color: rgba(255,255,255,0.7); text-decoration: none; transition: 0.3s; display: block; margin-bottom: 15px; font-size: 0.9rem; }
        .footer-link:hover { color: var(--primary-color); padding-left: 10px; }

        .footer-subscribe .input-group { background: rgba(255,255,255,0.08); backdrop-filter: blur(8px); border-radius: 12px; padding: 6px; border: 1px solid rgba(255,255,255,0.15); }
        .footer-subscribe .form-control { background: transparent !important; border: none; color: white !important; box-shadow: none; }
        .footer-subscribe .form-control::placeholder { color: rgba(255,255,255,0.4); }

        .btn-subscribe { background-color: var(--primary-color) !important; color: #fff !important; font-weight: 700 !important; border-radius: 10px !important; padding: 10px 25px !important; border: none; }
        .btn-subscribe:hover { background: var(--accent-yellow) !important; color: #000 !important; transform: scale(1.02); }

        .social-icons a { 
            width: 45px; height: 45px; background: rgba(255,255,255,0.08); 
            display: inline-flex; align-items: center; justify-content: center; 
            border-radius: 12px; color: white; margin-right: 12px; transition: 0.4s; 
            border: 1px solid rgba(255,255,255,0.1); text-decoration: none;
        }
        .social-icons a:hover { background: var(--primary-color); color: #fff; transform: translateY(-5px) rotate(8deg); }

        .copyright-border { border-top: 1px solid rgba(255,255,255,0.1); margin-top: 70px; padding-top: 30px; }

        .btn-primary { background-color: var(--primary-color); border-color: var(--primary-color); border-radius: 8px; font-weight: 600; padding: 8px 24px; }
        .toast-container { z-index: 9999; }
        main { flex: 1; }
    </style>
    @stack('styles')
</head>
<body>

<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
        <a class="navbar-brand" href="{{ url('/') }}">
            <i class="bi bi-droplet-stars me-2"></i>NATURE<span>SHOP</span>
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item"><a class="nav-link" href="{{ route('products.index') }}">Cửa hàng</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Về chúng tôi</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Liên hệ</a></li>
            </ul>
            <ul class="navbar-nav ms-auto align-items-center">
                @php
                    $cartCount = auth()->check()
                        ? \App\Models\Cart::where('user_id', auth()->id())->sum('quantity')
                        : count(session('cart', []));
                @endphp
                
                <li class="nav-item me-3">
                    <a class="nav-link cart-badge-wrapper" href="{{ route('cart.index') }}">
                        <i class="bi bi-bag-heart fs-4 text-dark"></i>
                        <span id="cart-count">{{ $cartCount }}</span>
                    </a>
                </li>

                @auth
                    @php
                        $recentOrders = \App\Models\Order::where('user_id', auth()->id())->latest()->take(3)->get();
                        $pendingCount = \App\Models\Order::where('user_id', auth()->id())->where('status','pending')->count();
                    @endphp
                    <li class="nav-item dropdown me-3">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-bell fs-4 text-dark"></i>
                            @if($pendingCount > 0)
                                <span class="badge bg-danger ms-1" id="order-count">{{ $pendingCount }}</span>
                            @endif
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg mt-3 animate__animated animate__fadeIn">
                            <li class="dropdown-header">Đơn hàng gần đây</li>
                            @forelse($recentOrders as $ro)
                                <li>
                                    <a class="dropdown-item d-flex justify-content-between align-items-center" href="{{ route('orders.show', $ro->id) }}">
                                        <div>
                                            <div class="small fw-bold">#{{ $ro->id }}</div>
                                            <div class="text-muted small">{{ number_format($ro->total_price,0,',','.') }} đ</div>
                                        </div>
                                        <span class="badge bg-{{ $ro->status === 'pending' ? 'warning' : 'success' }}">{{ $ro->status }}</span>
                                    </a>
                                </li>
                            @empty
                                <li class="dropdown-item text-muted">Không có đơn hàng</li>
                            @endforelse
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-center small text-primary" href="{{ route('orders.index') }}">Xem tất cả đơn hàng</a></li>
                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                            <div class="bg-success bg-opacity-10 rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                <i class="bi bi-person text-success"></i>
                            </div>
                            <span class="fw-bold">{{ auth()->user()->name }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg mt-3 animate__animated animate__fadeIn">
                            @if(auth()->user()->role === 'admin')
                                <li><a class="dropdown-item py-2" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2 me-2"></i>Quản trị</a></li>
                            @endif
                            <li><a class="dropdown-item py-2" href="{{ route('profile.index') }}"><i class="bi bi-person-circle me-2"></i>Hồ sơ</a></li>
                            
                            {{-- MỤC ĐÁNH GIÁ SẢN PHẨM ĐƯỢC THÊM TẠI ĐÂY --}}
                            <li><a class="dropdown-item py-2" href="{{ route('reviews.index') }}"><i class="bi bi-star-fill text-warning me-2"></i>Đánh giá của tôi</a></li>
                            
                            <li><hr class="dropdown-divider opacity-50"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button class="dropdown-item text-danger py-2" type="submit"><i class="bi bi-box-arrow-right me-2"></i>Đăng xuất</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @else
                    <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Đăng nhập</a></li>
                    @if (Route::has('register'))
                        <li class="nav-item">
                            <a href="{{ route('register') }}" class="btn btn-primary btn-sm ms-lg-3 shadow-sm px-3 py-2">Đăng ký</a>
                        </li>
                    @endif
                @endauth
            </ul>
        </div>
    </div>
</nav>

<main>
    @yield('content')
</main>

<div class="footer-section">
    <div class="footer-contact-bar">
        <div class="container">
            <div class="row text-center fw-bold">
                <a href="tel:+84123456789" class="col-md-4 contact-item mb-3 mb-md-0">
                    <i class="bi bi-telephone-plus-fill fs-4"></i> 
                    <span>HỖ TRỢ: +84 123 456 789</span>
                </a>
                <a href="mailto:hello@natureshop.vn" class="col-md-4 contact-item mb-3 mb-md-0">
                    <i class="bi bi-envelope-heart-fill fs-4"></i> 
                    <span>EMAIL: hello@natureshop.vn</span>
                </a>
                <div class="col-md-4 contact-item">
                    <i class="bi bi-geo-alt-fill fs-4"></i> 
                    <span>ĐỊA CHỈ: Thảo Điền, TP. HCM</span>
                </div>
            </div>
        </div>
    </div>

    <footer class="main-footer">
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-4 col-md-6">
                    <h5 class="footer-title">Về Nature Shop</h5>
                    <p class="small lh-lg opacity-75 mb-4">
                        Chúng tôi tự hào cung cấp các giải pháp mùi hương thuần khiết từ thiên nhiên. 
                        Từng giọt tinh dầu là một lời cam kết về chất lượng và sự an toàn cho gia đình bạn.
                    </p>
                    <div class="social-icons">
                        <a href="#"><i class="bi bi-facebook"></i></a>
                        <a href="#"><i class="bi bi-instagram"></i></a>
                        <a href="#"><i class="bi bi-tiktok"></i></a>
                        <a href="#"><i class="bi bi-youtube"></i></a>
                    </div>
                </div>

                <div class="col-lg-2 col-md-6">
                    <h5 class="footer-title">Sản Phẩm</h5>
                    <ul class="list-unstyled">
                        <li><a href="#" class="footer-link">Tinh dầu nguyên chất</a></li>
                        <li><a href="#" class="footer-link">Tinh dầu hỗn hợp</a></li>
                        <li><a href="#" class="footer-link">Máy khuếch tán</a></li>
                        <li><a href="#" class="footer-link">Quà tặng thiên nhiên</a></li>
                    </ul>
                </div>

                <div class="col-lg-2 col-md-6">
                    <h5 class="footer-title">Thông Tin</h5>
                    <ul class="list-unstyled">
                        <li><a href="#" class="footer-link">Chính sách bảo mật</a></li>
                        <li><a href="#" class="footer-link">Vận chuyển & Giao hàng</a></li>
                        <li><a href="#" class="footer-link">Câu hỏi thường gặp</a></li>
                        <li><a href="#" class="footer-link">Tuyển dụng</a></li>
                    </ul>
                </div>

                <div class="col-lg-4 col-md-6">
                    <h5 class="footer-title">Đăng Ký Bản Tin</h5>
                    <p class="small opacity-75 mb-4">Nhận thông tin sớm nhất về sản phẩm mới và kiến thức chăm sóc sức khỏe.</p>
                    <div class="footer-subscribe">
                        <form action="#" class="input-group">
                            <input type="email" class="form-control" placeholder="Email của bạn...">
                            <button class="btn btn-subscribe" type="submit">ĐĂNG KÝ</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="row copyright-border align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <p class="small mb-0 opacity-50">&copy; {{ date('Y') }} <strong>Nature Shop</strong>. Nâng tầm không gian sống thuần khiết.</p>
                </div>
                <div class="col-md-6 text-center text-md-end mt-3 mt-md-0">
                    <img src="https://theme.hstatic.net/1000069962/1000395433/14/method_share.png?v=334" height="22" alt="Payment" style="filter: brightness(0) invert(1) opacity(0.6);">
                </div>
            </div>
        </div>
    </footer>
</div>

<div class="toast-container position-fixed top-0 end-0 p-3">
    @if(session('message') || session('success') || session('error'))
    <div class="toast show align-items-center text-bg-{{ session('error') ? 'danger' : 'success' }} border-0 mb-2 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body px-3 py-2">
                <i class="bi bi-{{ session('error') ? 'exclamation-circle' : 'check-circle' }}-fill me-2"></i>
                {{ session('message') ?? session('success') ?? session('error') }}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
    @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function updateCartUI(count) {
        const el = document.getElementById('cart-count');
        if (el) {
            el.textContent = count;
            el.classList.add('animate__animated', 'animate__rubberBand');
            el.addEventListener('animationend', () => {
                el.classList.remove('animate__animated', 'animate__rubberBand');
            }, {once: true});
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        var toastElements = [].slice.call(document.querySelectorAll('.toast'));
        toastElements.forEach(function(toastEl) {
            var bsToast = new bootstrap.Toast(toastEl, { autohide: true, delay: 4000 });
            bsToast.show();
        });
    });
</script>
@stack('scripts')
</body>
</html>