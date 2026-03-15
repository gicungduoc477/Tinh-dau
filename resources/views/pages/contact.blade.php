@extends('layouts.app')

@section('title', 'Liên hệ - Nature Shop')

@section('content')
<div class="container py-5">
    <div class="text-center mb-5">
        <h2 class="fw-bold">Liên hệ với chúng tôi</h2>
        <p class="text-muted">Chúng tôi luôn sẵn sàng lắng nghe ý kiến từ bạn</p>
    </div>

    <div class="row g-4">
        {{-- Thông tin liên hệ --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                <div class="d-flex mb-4">
                    <div class="bg-success bg-opacity-10 p-3 rounded-3 me-3">
                        <i class="bi bi-geo-alt text-success fs-4"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1">Địa chỉ</h6>
                        <p class="small text-muted mb-0">123 Đường Thiên Nhiên, Quận 1, TP. Hồ Chí Minh</p>
                    </div>
                </div>
                <div class="d-flex mb-4">
                    <div class="bg-success bg-opacity-10 p-3 rounded-3 me-3">
                        <i class="bi bi-telephone text-success fs-4"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1">Điện thoại</h6>
                        <p class="small text-muted mb-0">0123 456 789</p>
                    </div>
                </div>
                <div class="d-flex mb-0">
                    <div class="bg-success bg-opacity-10 p-3 rounded-3 me-3">
                        <i class="bi bi-envelope text-success fs-4"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1">Email</h6>
                        <p class="small text-muted mb-0">contact@natureshop.com</p>
                    </div>
                </div>
                <hr class="my-4">
                <h6 class="fw-bold mb-3">Theo dõi chúng tôi</h6>
                <div class="d-flex gap-2">
                    <a href="#" class="btn btn-outline-success btn-sm rounded-circle"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="btn btn-outline-success btn-sm rounded-circle"><i class="bi bi-instagram"></i></a>
                    <a href="#" class="btn btn-outline-success btn-sm rounded-circle"><i class="bi bi-youtube"></i></a>
                </div>
            </div>
        </div>

        {{-- Form liên hệ --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <form action="#" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Họ và tên</label>
                            <input type="text" class="form-control rounded-3" placeholder="Nhập tên của bạn">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Email</label>
                            <input type="email" class="form-control rounded-3" placeholder="Nhập email">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Tiêu đề</label>
                            <input type="text" class="form-control rounded-3" placeholder="Vấn đề bạn cần hỗ trợ">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Nội dung</label>
                            <textarea class="form-control rounded-3" rows="4" placeholder="Viết lời nhắn của bạn..."></textarea>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-success rounded-pill px-5 fw-bold shadow-sm">
                                Gửi tin nhắn <i class="bi bi-send ms-2"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection