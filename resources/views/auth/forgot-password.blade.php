@extends('layouts.app')

@section('title', 'Quên mật khẩu - Nature Shop')

@section('content')
<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-5">
            <div class="card shadow-lg border-0 rounded-lg">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <h3 class="font-weight-bold text-success">QUÊN MẬT KHẨU?</h3>
                        <p class="text-muted small">Nhập email của bạn và chúng tôi sẽ gửi liên kết đặt lại mật khẩu mới.</p>
                    </div>

                    @if (session('message'))
                        <div class="alert alert-success border-0 shadow-sm small">
                            <i class="fas fa-paper-plane me-2"></i> {{ session('message') }}
                        </div>
                    @endif

                    <form action="{{ route('password.email') }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label class="form-label fw-bold small">Địa chỉ Email</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                                   placeholder="name@example.com" value="{{ old('email') }}" required autofocus>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-success fw-bold py-2">
                                Gửi liên kết xác nhận
                            </button>
                        </div>

                        <div class="text-center mt-4">
                            <a href="{{ route('login') }}" class="text-decoration-none small text-muted">
                                <i class="fas fa-chevron-left me-1"></i> Quay lại Đăng nhập
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection