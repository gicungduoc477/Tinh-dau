@extends('layouts.app')

@section('title', 'Đăng nhập - Nature Shop')

@section('content')
<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-5">
            <div class="card shadow-lg border-0 rounded-lg">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <h3 class="font-weight-bold text-success">ĐĂNG NHẬP</h3>
                        <p class="text-muted">Chào mừng bạn quay trở lại với Nature Shop!</p>
                    </div>

                    {{-- Thông báo thành công (Khi vừa reset mật khẩu hoặc đăng ký xong) --}}
                    @if(session('message'))
                        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                            <i class="fas fa-check-circle me-2"></i> {{ session('message') }}
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

                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        {{-- Email hoặc Số điện thoại --}}
                        <div class="mb-3">
                            <label for="identifier" class="form-label fw-bold">Email hoặc Số điện thoại</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fas fa-user text-muted"></i></span>
                                <input id="identifier" 
                                       name="identifier" 
                                       type="text" 
                                       class="form-control border-start-0 @error('identifier') is-invalid @enderror" 
                                       value="{{ old('identifier') }}" 
                                       placeholder="example@gmail.com"
                                       required 
                                       autofocus>
                            </div>
                            @error('identifier')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Mật khẩu --}}
                        <div class="mb-3">
                            <label for="password" class="form-label fw-bold">Mật khẩu</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fas fa-lock text-muted"></i></span>
                                <input id="password" 
                                       name="password" 
                                       type="password" 
                                       class="form-control border-start-0 @error('password') is-invalid @enderror" 
                                       placeholder="••••••••"
                                       required>
                            </div>
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Ghi nhớ & Quên mật khẩu --}}
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check">
                                <input type="checkbox" name="remember" id="remember" class="form-check-input cursor-pointer shadow-none">
                                <label for="remember" class="form-check-label cursor-pointer small text-muted">Ghi nhớ tôi</label>
                            </div>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-decoration-none small fw-bold text-success hover-underline">
                                    Quên mật khẩu?
                                </a>
                            @endif
                        </div>

                        {{-- Nút Đăng nhập --}}
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success py-2 fw-bold shadow-sm btn-login">
                                <i class="fas fa-sign-in-alt me-2"></i> Đăng nhập ngay
                            </button>
                        </div>
                        
                        {{-- Chuyển sang Đăng ký --}}
                        <div class="text-center mt-4">
                            <span class="text-muted small">Chưa có tài khoản? </span>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="text-decoration-none fw-bold text-success small">Đăng ký thành viên</a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
            
            {{-- Hỗ trợ quay lại trang chủ --}}
            <div class="text-center mt-4">
                <a href="{{ url('/') }}" class="text-muted text-decoration-none small transition-all">
                    <i class="fas fa-long-arrow-alt-left me-1"></i> Quay lại trang chủ
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    body { background-color: #f8f9fa; }
    .cursor-pointer { cursor: pointer; }
    .text-success { color: #27ae60 !important; }
    .btn-success { background-color: #27ae60; border-color: #27ae60; transition: all 0.3s ease; }
    .btn-success:hover { background-color: #219150; border-color: #219150; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(39, 174, 96, 0.2); }
    .form-control:focus { border-color: #27ae60; box-shadow: 0 0 0 0.2rem rgba(39, 174, 96, 0.15); }
    .hover-underline:hover { text-decoration: underline !important; }
    .transition-all { transition: all 0.3s; }
    .transition-all:hover { color: #27ae60 !important; }
    .card { border-radius: 15px; }
    .input-group-text { border-right: none; }
</style>
@endsection