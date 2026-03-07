@extends('layouts.app')

@section('title', 'Đặt lại mật khẩu - Nature Shop')

@section('content')
<div class="container">
    <div class="row justify-content-center mt-5 mb-5">
        <div class="col-md-5">
            <div class="card shadow-lg border-0 rounded-lg">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <h3 class="font-weight-bold text-success">MẬT KHẨU MỚI</h3>
                        <p class="text-muted small">Vui lòng thiết lập mật khẩu mới cho tài khoản của bạn.</p>
                    </div>

                    {{-- Hiển thị thông báo lỗi chung nếu có --}}
                    @if ($errors->has('email'))
                        <div class="alert alert-danger small py-2">
                            {{ $errors->first('email') }}
                        </div>
                    @endif

                    <form action="{{ route('password.update') }}" method="POST">
                        @csrf
                        
                        {{-- Token quan trọng để Laravel xác thực yêu cầu reset --}}
                        <input type="hidden" name="token" value="{{ $token }}">

                        <div class="mb-3">
                            <label class="form-label fw-bold small">Email xác nhận</label>
                            {{-- Input email để readonly để tránh người dùng sửa đổi làm sai lệch token --}}
                            <input type="email" name="email" class="form-control bg-light" 
                                   value="{{ $email ?? old('email') }}" required readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small">Mật khẩu mới</label>
                            <input type="password" name="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   placeholder="Tối thiểu 8 ký tự" required autofocus>
                            @error('password') 
                                <div class="invalid-feedback small">{{ $message }}</div> 
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold small">Xác nhận mật khẩu</label>
                            <input type="password" name="password_confirmation" 
                                   class="form-control" 
                                   placeholder="Nhập lại mật khẩu" required>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-success fw-bold py-2 shadow-sm">
                                Cập nhật mật khẩu
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="text-center mt-3">
                <a href="{{ route('login') }}" class="text-decoration-none small text-success">
                    <i class="fas fa-arrow-left"></i> Quay lại đăng nhập
                </a>
            </div>
        </div>
    </div>
</div>
@endsection