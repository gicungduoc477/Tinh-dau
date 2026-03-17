@extends('layouts.app')

@section('title', 'Đăng ký thành viên - Nature Shop')

@section('content')
<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-6">
            <div class="card shadow-lg border-0 rounded-lg animate__animated animate__fadeInUp">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <h3 class="font-weight-bold text-success">ĐĂNG KÝ</h3>
                        <p class="text-muted small">Trở thành thành viên của Nature Shop để nhận nhiều ưu đãi.</p>
                    </div>

                    {{-- Thông báo thành công --}}
                    @if(session('message'))
                        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                            <i class="bi bi-check-circle-fill me-2"></i> {{ session('message') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    {{-- Hiển thị lỗi tổng quát --}}
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                            <ul class="mb-0 small">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('register') }}">
                        @csrf
                        
                        {{-- Họ và tên --}}
                        <div class="mb-3">
                            <label for="name" class="form-label fw-bold small">Họ và tên</label>
                            <div class="input-group shadow-sm">
                                <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-person"></i></span>
                                <input id="name" name="name" type="text" class="form-control border-start-0 ps-0 @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Nguyễn Văn A" required autofocus>
                            </div>
                            @error('name') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="row">
                            {{-- Email --}}
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label fw-bold small">Email</label>
                                <input id="email" name="email" type="email" class="form-control shadow-sm @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="mail@example.com">
                                @error('email') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                            {{-- Số điện thoại --}}
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label fw-bold small">Số điện thoại</label>
                                <input id="phone" name="phone" type="text" class="form-control shadow-sm @error('phone') is-invalid @enderror" value="{{ old('phone') }}" placeholder="09xxxxxxx">
                                @error('phone') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        {{-- Mật khẩu --}}
                        <div class="mb-3">
                            <label for="password" class="form-label fw-bold small">Mật khẩu</label>
                            <div class="input-group shadow-sm">
                                <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-lock"></i></span>
                                <input id="password" name="password" type="password" class="form-control border-start-0 border-end-0 ps-0 @error('password') is-invalid @enderror" placeholder="Tối thiểu 8 ký tự" required>
                                <button class="btn btn-white border border-start-0 text-muted toggle-password" type="button" data-target="password">
                                    <i class="bi bi-eye-slash"></i>
                                </button>
                            </div>
                            <div class="form-text small" style="font-size: 0.75rem;">Mật khẩu cần có chữ hoa, số và ký tự đặc biệt.</div>
                            @error('password') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        {{-- Xác nhận mật khẩu --}}
                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label fw-bold small">Xác nhận mật khẩu</label>
                            <div class="input-group shadow-sm">
                                <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-shield-check"></i></span>
                                <input id="password_confirmation" name="password_confirmation" type="password" class="form-control border-start-0 border-end-0 ps-0" placeholder="Nhập lại mật khẩu" required>
                                <button class="btn btn-white border border-start-0 text-muted toggle-password" type="button" data-target="password_confirmation">
                                    <i class="bi bi-eye-slash"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Điều khoản --}}
                        <div class="mb-4 form-check">
                            <input id="agree_terms" name="agree_terms" type="checkbox" class="form-check-input cursor-pointer shadow-none @error('agree_terms') is-invalid @enderror" value="1" {{ old('agree_terms') ? 'checked' : '' }}>
                            <label for="agree_terms" class="form-check-label cursor-pointer small text-muted">Tôi đồng ý với <a href="#" class="text-success text-decoration-none fw-bold">Điều khoản</a> & <a href="#" class="text-success text-decoration-none fw-bold">Chính sách</a></label>
                            @error('agree_terms') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        {{-- Nút Đăng ký --}}
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success py-2 fw-bold shadow-sm btn-login">
                                <i class="bi bi-person-plus-fill me-2"></i> TẠO TÀI KHOẢN NGAY
                            </button>
                        </div>
                    </form>

                    <div class="text-center mt-4">
                        <span class="text-muted small">Đã có tài khoản? </span>
                        <a href="{{ route('login') }}" class="text-decoration-none fw-bold text-success small">Đăng nhập tại đây</a>
                    </div>
                </div>
            </div>

            <div class="text-center mt-4">
                <a href="{{ url('/') }}" class="text-muted text-decoration-none small transition-all">
                    <i class="bi bi-arrow-left me-1"></i> Quay lại trang chủ
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    body { background-color: #f8f9fa; }
    .cursor-pointer { cursor: pointer; }
    .text-success { color: #27ae60 !important; }
    .btn-success { background-color: #27ae60; border-color: #27ae60; transition: all 0.3s ease; border-radius: 10px; }
    .btn-success:hover { background-color: #219150; border-color: #219150; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(39, 174, 96, 0.3) !important; }
    .card { border-radius: 20px; }
    .form-control { border-radius: 10px; padding: 10px 15px; }
    .form-control:focus { border-color: #27ae60; box-shadow: none; }
    .input-group-text { border-radius: 10px 0 0 10px !important; }
    .toggle-password { border-radius: 0 10px 10px 0 !important; background: #fff; border-color: #dee2e6; }
    .toggle-password:hover { color: #27ae60 !important; }
    .transition-all:hover { color: #27ae60 !important; }
</style>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggleButtons = document.querySelectorAll('.toggle-password');
        
        toggleButtons.forEach(button => {
            button.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const input = document.getElementById(targetId);
                const icon = this.querySelector('i');
                
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.replace('bi-eye-slash', 'bi-eye');
                    this.classList.add('text-success');
                } else {
                    input.type = 'password';
                    icon.classList.replace('bi-eye', 'bi-eye-slash');
                    this.classList.remove('text-success');
                }
            });
        });
    });
</script>
@endpush
@endsection