@extends('admin.layout.admin_layout')

@section('title', 'Thêm người dùng mới')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Quản lý thành viên</h1>
        <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Quay lại danh sách
        </a>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Tạo tài khoản mới</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.users.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="font-weight-bold">Họ và tên <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       value="{{ old('name') }}" placeholder="Nhập tên đầy đủ...">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="email" class="font-weight-bold">Địa chỉ Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" id="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       value="{{ old('email') }}" placeholder="example@gmail.com">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="font-weight-bold">Mật khẩu <span class="text-danger">*</span></label>
                                <input type="password" name="password" id="password" 
                                       class="form-control @error('password') is-invalid @enderror">
                                <small class="text-muted">Tối thiểu 8 ký tự.</small>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="font-weight-bold">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="role" class="font-weight-bold">Phân quyền <span class="text-danger">*</span></label>
                                <select name="role" id="role" class="form-control @error('role') is-invalid @enderror">
                                    <option value="">-- Chọn vai trò --</option>
                                    <option value="user" {{ old('role') == 'user' ? 'selected' : '' }}>Người dùng (User)</option>
                                    <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Quản trị viên (Admin)</option>
                                </select>
                                @error('role')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-save mr-1"></i> Lưu thông tin
                            </button>
                            <button type="reset" class="btn btn-light px-4">Làm mới</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection