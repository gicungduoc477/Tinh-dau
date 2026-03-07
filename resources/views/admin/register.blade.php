@extends('layouts.app')

@section('title', 'Admin Register')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <h3 class="mb-3">Tạo tài khoản quản trị</h3>

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

        <form method="POST" action="{{ route('admin.register') }}">
            @csrf
            <div class="mb-3">
                <label for="name" class="form-label">Họ và tên</label>
                <input id="name" name="name" type="text" class="form-control" value="{{ old('name') }}" required autofocus>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input id="email" name="email" type="email" class="form-control" value="{{ old('email') }}" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Mật khẩu</label>
                <input id="password" name="password" type="password" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="password_confirmation" class="form-label">Xác nhận mật khẩu</label>
                <input id="password_confirmation" name="password_confirmation" type="password" class="form-control" required>
            </div>

            <button class="btn btn-success">Tạo quản trị</button>
        </form>

        <hr>
        <p>Trở về giao diện khách? <a href="{{ route('register') }}">Đăng ký khách</a></p>
    </div>
</div>
@endsection