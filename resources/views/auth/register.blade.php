@extends('layouts.app')

@section('title', 'Register')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <h3 class="mb-3">Đăng ký</h3>

        @if(session('message'))
            <div class="alert alert-success">{{ session('message') }}</div>
        @endif

        {{-- Support both MessageBag and plain array errors from controller --}}
        @if(!empty($errors) && is_array($errors))
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @elseif($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}">
            @csrf
            <div class="mb-3">
                <label for="name" class="form-label">Họ và tên</label>
                <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required autofocus>
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email <small class="text-muted">(hoặc để trống nếu dùng số điện thoại)</small></label>
                <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}">
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label for="phone" class="form-label">Số điện thoại <small class="text-muted">(hoặc để trống nếu dùng email)</small></label>
                <input id="phone" name="phone" type="text" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}">
                @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Mật khẩu</label>
                <input id="password" name="password" type="password" class="form-control @error('password') is-invalid @enderror" required>
                <div class="form-text">Mật khẩu phải tối thiểu 8 ký tự, có chữ hoa, chữ thường, số và ký tự đặc biệt</div>
                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label for="password_confirmation" class="form-label">Xác nhận mật khẩu</label>
                <input id="password_confirmation" name="password_confirmation" type="password" class="form-control" required>
            </div>

            <div class="mb-3 form-check">
                <input id="agree_terms" name="agree_terms" type="checkbox" class="form-check-input @error('agree_terms') is-invalid @enderror" value="1" {{ old('agree_terms') ? 'checked' : '' }}>
                <label for="agree_terms" class="form-check-label">Tôi đồng ý với <a href="#">Điều khoản sử dụng</a> & <a href="#">Chính sách bảo mật</a></label>
                @error('agree_terms') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <button class="btn btn-success">Đăng ký</button>
        </form>

        <hr>
        <p>Đã có tài khoản? <a href="{{ route('login') }}">Đăng nhập</a></p>
    </div>
</div>
@endsection