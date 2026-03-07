@extends('admin.layout.admin_layout')

@section('title', 'Danh sách người dùng')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-users mr-1"></i> Quản lý người dùng
            </h6>
            <div class="d-flex align-items-center">
                <span class="badge badge-secondary mr-3">Tổng số: {{ $users->total() }}</span>
                <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm shadow-sm">
                    <i class="fas fa-plus fa-sm text-white-50"></i> Thêm người dùng mới
                </a>
            </div>
        </div>
        
        <div class="card-body">
            {{-- Thông báo thành công --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            {{-- Thông báo lỗi (ví dụ: tự xóa chính mình) --}}
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th width="5%">ID</th>
                            <th>Họ tên</th>
                            <th>Email</th>
                            <th width="10%">Vai trò</th>
                            <th width="15%">Ngày tạo</th>
                            <th width="15%" class="text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td><strong>{{ $user->name }}</strong></td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @if($user->role === 'admin')
                                        <span class="badge badge-danger px-3 py-2">ADMIN</span>
                                    @else
                                        <span class="badge badge-info px-3 py-2">USER</span>
                                    @endif
                                </td>
                                <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                                <td class="text-center">
                                    {{-- Nút Sửa --}}
                                    <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-sm btn-warning btn-circle" title="Chỉnh sửa">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    {{-- Nút Liên hệ --}}
                                    <a href="mailto:{{ $user->email }}" class="btn btn-sm btn-info btn-circle" title="Gửi email">
                                        <i class="fas fa-envelope"></i>
                                    </a>

                                    {{-- Nút Xóa (Sử dụng Form để bảo mật method DELETE) --}}
                                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa người dùng này? Thao tác này không thể hoàn tác.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger btn-circle" title="Xóa người dùng">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Không có dữ liệu người dùng.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Phân trang --}}
            <div class="mt-4 d-flex justify-content-center">
                {{ $users->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>
@endsection