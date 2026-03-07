@extends('layouts.app')

@section('title', 'Profile')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <h3 class="mb-3">Hồ sơ của tôi</h3>

        @if(session('message'))
            <div class="alert alert-success">{{ session('message') }}</div>
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

        <div class="card mb-4">
            <div class="card-body">
                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="name" class="form-label">Họ và tên</label>
                        <input id="name" name="name" type="text" class="form-control" value="{{ old('name', $user->name) }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input id="email" name="email" type="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                    </div>

                    <button class="btn btn-primary">Cập nhật thông tin</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Đổi mật khẩu</div>
            <div class="card-body">
                <form method="POST" action="{{ route('profile.password') }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="current_password" class="form-label">Mật khẩu hiện tại</label>
                        <input id="current_password" name="current_password" type="password" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Mật khẩu mới</label>
                        <input id="password" name="password" type="password" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Xác nhận mật khẩu</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" class="form-control" required>
                    </div>

                    <button class="btn btn-warning">Đổi mật khẩu</button>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection