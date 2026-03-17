@extends('layouts.app')

@section('title', 'Profile')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h3 class="mb-4 text-primary"><i class="fas fa-user-circle"></i> Hồ sơ của tôi</h3>

            @if(session('message'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('message') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger shadow-sm">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white font-weight-bold">Thông tin tài khoản</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('profile.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label font-weight-bold">Họ và tên</label>
                            <input id="name" name="name" type="text" class="form-control" value="{{ old('name', $user->name) }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label font-weight-bold">Email</label>
                            <input id="email" name="email" type="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                        </div>

                        <button type="submit" class="btn btn-primary">Cập nhật thông tin</button>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm border-warning">
                <div class="card-header bg-warning text-dark font-weight-bold">Đổi mật khẩu bảo mật</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('profile.password') }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="current_password" class="form-label font-weight-bold">Mật khẩu hiện tại</label>
                            <input id="current_password" name="current_password" type="password" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label font-weight-bold">Mật khẩu mới</label>
                            <input id="password" name="password" type="password" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label font-weight-bold">Xác nhận mật khẩu mới</label>
                            <input id="password_confirmation" name="password_confirmation" type="password" class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-warning font-weight-bold">Lưu mật khẩu mới</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection