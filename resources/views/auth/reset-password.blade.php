@extends('layouts.app')

@section('title', 'Đặt lại mật khẩu - Nature Shop')

@section('content')
<div class="container">
    <div class="row justify-content-center mt-5 mb-5">
        <div class="col-md-5">
            <div class="card shadow-lg border-0 rounded-lg animate__animated animate__fadeIn">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <div class="mb-3">
                            <i class="bi bi-shield-lock text-success" style="font-size: 3rem;"></i>
                        </div>
                        <h3 class="font-weight-bold text-success">MẬT KHẨU MỚI</h3>
                        <p class="text-muted small">Vui lòng thiết lập mật khẩu mới cho tài khoản của bạn.</p>
                    </div>

                    {{-- Hiển thị thông báo lỗi chung --}}
                    @if ($errors->has('email'))
                        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm small" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ $errors->first('email') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('password.update') }}" method="POST">
                        @csrf
                        
                        {{-- Token quan trọng để Laravel xác thực yêu cầu reset --}}
                        <input type="hidden" name="token" value="{{ $token }}">

                        {{-- Email (Readonly) --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Email xác nhận</label>
                            <div class="input-group shadow-sm">
                                <span class="input-group-text bg-light border-end-0 text-muted">
                                    <i class="bi bi-envelope"></i>
                                </span>
                                <input type="email" name="email" class="form-control bg-light border-start-0 ps-0" 
                                       value="{{ $email ?? old('email') }}" required readonly>
                            </div>
                        </div>

                        {{-- Mật khẩu mới --}}
                        <div class="mb-3">
                            <label for="password" class="form-label fw-bold small text-muted">Mật khẩu mới</label>
                            <div class="input-group shadow-sm">
                                <span class="input-group-text bg-white border-end-0 text-muted">
                                    <i class="bi bi-key"></i>
                                </span>
                                <input id="password" 
                                       type="password" 
                                       name="password" 
                                       class="form-control border-start-0 border-end-0 ps-0 @error('password') is-invalid @enderror" 
                                       placeholder="Tối thiểu 8 ký tự" required autofocus>
                                <button class="btn btn-white border border-start-0 text-muted toggle-password" type="button" data-target="password">
                                    <i class="bi bi-eye-slash"></i>
                                </button>
                            </div>
                            @error('password') 
                                <div class="invalid-feedback d-block small">{{ $message }}</div> 
                            @enderror
                        </div>

                        {{-- Xác nhận mật khẩu mới --}}
                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label fw-bold small text-muted">Xác nhận mật khẩu</label>
                            <div class="input-group shadow-sm">
                                <span class="input-group-text bg-white border-end-0 text-muted">
                                    <i class="bi bi-check2-circle"></i>
                                </span>
                                <input id="password_confirmation" 
                                       type="password" 
                                       name="password_confirmation" 
                                       class="form-control border-start-0 border-end-0 ps-0" 
                                       placeholder="Nhập lại mật khẩu mới" required>
                                <button class="btn btn-white border border-start-0 text-muted toggle-password" type="button" data-target="password_confirmation">
                                    <i class="bi bi-eye-slash"></i>
                                </button>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success fw-bold py-2 shadow-sm btn-login">
                                <i class="bi bi-arrow-repeat me-2"></i> CẬP NHẬT MẬT KHẨU
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <a href="{{ route('login') }}" class="text-muted text-decoration-none small transition-all">
                    <i class="bi bi-arrow-left me-1"></i> Quay lại đăng nhập
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    body { background-color: #f8f9fa; }
    .text-success { color: #27ae60 !important; }
    .btn-success { 
        background-color: #27ae60; 
        border-color: #27ae60; 
        transition: all 0.3s ease; 
        border-radius: 10px;
    }
    .btn-success:hover { 
        background-color: #219150; 
        border-color: #219150; 
        transform: translateY(-2px); 
        box-shadow: 0 5px 15px rgba(39, 174, 96, 0.3) !important; 
    }
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