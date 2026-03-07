@extends('admin.layout.admin_layout')
@section('title', 'Chỉnh sửa người dùng')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Chỉnh sửa: {{ $user->name }}</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
            @csrf @method('PUT')
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Tên</label>
                    <input type="text" name="name" class="form-control" value="{{ $user->name }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Vai trò</label>
                    <select name="role" class="form-control">
                        <option value="user" {{ $user->role == 'user' ? 'selected' : '' }}>User</option>
                        <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Mật khẩu mới (Để trống nếu không đổi)</label>
                    <input type="password" name="password" class="form-control">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Xác nhận mật khẩu mới</label>
                    <input type="password" name="password_confirmation" class="form-control">
                </div>
            </div>
            <button type="submit" class="btn btn-success">Cập nhật</button>
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Hủy</a>
        </form>
    </div>
</div>
@endsection