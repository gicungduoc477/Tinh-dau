@extends('layouts.app')

@section('title', 'Về chúng tôi - Nature Shop')

@section('content')
<div class="about-page">
    {{-- Banner Section --}}
    <div class="bg-light py-5 mb-5">
        <div class="container text-center">
            <h1 class="fw-bold display-4 text-dark">Về Chúng Tôi</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb justify-content-center">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-success text-decoration-none">Trang chủ</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Về chúng tôi</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="container">
        {{-- Story Section --}}
        <div class="row align-items-center mb-5">
            <div class="col-md-6 mb-4 mb-md-0">
                <h6 class="text-success fw-bold text-uppercase ls-wide">Câu chuyện của chúng tôi</h6>
                <h2 class="fw-bold mb-4 display-6">Mang tinh hoa thiên nhiên <br><span class="text-success">vào không gian sống</span></h2>
                <p class="text-muted lh-lg">Nature Shop được thành lập với mong muốn mang lại những giá trị thuần khiết nhất từ thiên nhiên. Chúng tôi tin rằng, mỗi giọt tinh dầu hay sản phẩm thảo mộc đều chứa đựng năng lượng chữa lành kỳ diệu.</p>
                <p class="text-muted lh-lg">Sứ mệnh của chúng tôi là giúp bạn tìm thấy sự thư giãn, giảm căng thẳng và cải thiện chất lượng giấc ngủ sau những giờ làm việc mệt mỏi thông qua các liệu pháp hương thơm (Aromatherapy).</p>
                
                <div class="row g-3 mt-3">
                    <div class="col-6">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                            <span class="fw-medium">Kiểm định an toàn</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                            <span class="fw-medium">Thảo mộc 100%</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 text-center">
                <div class="position-relative d-inline-block">
                    {{-- Hình ảnh chính - Sử dụng link ảnh chuyên dụng --}}
                    <img src="https://img.freepik.com/free-photo/spa-treatment-composition-with-essential-oils_23-2148761595.jpg" 
                         class="img-fluid rounded-4 shadow-lg main-about-img" 
                         alt="Về chúng tôi"
                         style="max-height: 450px; width: 100%; object-fit: cover;">
                    {{-- Decorative box --}}
                    <div class="position-absolute bottom-0 start-0 bg-success text-white p-4 rounded-4 shadow d-none d-lg-block mb-n3 ms-n3">
                        <h3 class="fw-bold mb-0">5+</h3>
                        <p class="small mb-0">Năm kinh nghiệm</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Features Section --}}
        <div class="row g-4 py-5">
            <div class="col-md-4">
                <div class="feature-card p-4 bg-white rounded-4 shadow-sm h-100 border-bottom border-success border-4 text-center">
                    <div class="icon-box mb-3 mx-auto">
                        <i class="bi bi-shield-check text-success"></i>
                    </div>
                    <h5 class="fw-bold">100% Tự nhiên</h5>
                    <p class="small text-muted mb-0">Sản phẩm chiết xuất hoàn toàn từ thảo mộc, không hóa chất độc hại, an toàn cho mọi lứa tuổi.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card p-4 bg-white rounded-4 shadow-sm h-100 border-bottom border-success border-4 text-center">
                    <div class="icon-box mb-3 mx-auto">
                        <i class="bi bi-heart text-success"></i>
                    </div>
                    <h5 class="fw-bold">Tận tâm phục vụ</h5>
                    <p class="small text-muted mb-0">Đội ngũ chuyên viên luôn lắng nghe và tư vấn giải pháp phù hợp nhất cho sức khỏe của bạn.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card p-4 bg-white rounded-4 shadow-sm h-100 border-bottom border-success border-4 text-center">
                    <div class="icon-box mb-3 mx-auto">
                        <i class="bi bi-truck text-success"></i>
                    </div>
                    <h5 class="fw-bold">Giao hàng nhanh</h5>
                    <p class="small text-muted mb-0">Hỗ trợ vận chuyển nhanh toàn quốc với quy trình đóng gói chống sốc vô cùng cẩn thận.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .about-page { background-color: #fff; }
    .ls-wide { letter-spacing: 2px; }
    .feature-card { transition: transform 0.3s ease; }
    .feature-card:hover { transform: translateY(-10px); }
    .icon-box {
        width: 70px;
        height: 70px;
        background: #f0fdf4;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
    }
    .main-about-img {
        border: 8px solid #fff;
    }
    .mb-n3 { margin-bottom: -1rem !important; }
    .ms-n3 { margin-left: -1rem !important; }
</style>
@endsection