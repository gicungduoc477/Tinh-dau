@extends('layouts.app')

@section('title', 'Admin Login')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm p-4">
            <h3 class="mb-3 text-center">Đăng nhập quản trị</h3>

            @if(session('message'))
                <div class="alert alert-success">{{ session('message') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login') }}">
                @csrf
                
                <div class="mb-3">
                    <label for="identifier" class="form-label">Email hoặc Số điện thoại</label>
                    <input id="identifier" name="identifier" type="text" 
                           class="form-control @error('identifier') is-invalid @enderror" 
                           value="{{ old('identifier') }}" 
                           placeholder="Nhập email hoặc số điện thoại"
                           required autofocus>
                    @error('identifier') 
                        <div class="invalid-feedback">{{ $message }}</div> 
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Mật khẩu</label>
                    <input id="password" name="password" type="password" 
                           class="form-control @error('password') is-invalid @enderror" 
                           placeholder="Nhập mật khẩu"
                           required>
                    @error('password') 
                        <div class="invalid-feedback">{{ $message }}</div> 
                    @enderror
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" name="remember" id="remember" class="form-check-input">
                    <label for="remember" class="form-check-label">Ghi nhớ đăng nhập</label>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <button type="submit" class="btn btn-primary px-4">Đăng nhập</button>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-decoration-none">Quên mật khẩu?</a>
                    @endif
                </div>
            </form>

            <hr>
            <div class="text-center">
                <p class="mb-0">Trở về giao diện khách? <a href="{{ route('login') }}" class="fw-bold">Đăng nhập khách</a></p>
            </div>
        </div>
    </div>
</div>
@endsection