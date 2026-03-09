@extends('admin.layout.admin_layout')

@section('title')
    Quản lý sản phẩm
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Danh mục sản phẩm</h1>
        <div class="d-flex">
            <form class="form-inline mr-2" method="GET" action="{{ route('admin.product.index') }}">
                <div class="input-group">
                    <input type="search" class="form-control" name="q" placeholder="Tìm theo tên..." value="{{ request('q') }}">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>
            <button class="btn btn-primary shadow-sm" data-toggle="modal" data-target="#addProductModal">
                <i class="fas fa-plus fa-sm text-white-50"></i> Thêm sản phẩm mới
            </button>
        </div>
    </div>

    @if(session('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>Thành công!</strong> {{ session('message') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Danh sách sản phẩm hiện có</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th style="width: 50px;">STT</th>
                            <th style="width: 100px;">Hình ảnh</th>
                            <th>Tên sản phẩm</th>
                            <th>Giá bán</th>
                            <th>Trạng thái</th>
                            <th>Phân loại</th>
                            <th style="width: 120px;">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $key => $pro)
                        <tr>
                            <td>{{ $products->firstItem() + $key }}</td>
                            <td class="text-center">
                                <div style="width: 60px; height: 60px; margin: 0 auto; overflow: hidden; background: #f8f9fc; border: 1px solid #ddd; border-radius: 5px;">
                                    {{-- ĐƯỜNG DẪN ẢNH ĐÃ FIX CHO RENDER --}}
                                    <img src="{{ asset('uploads/product/' . $pro->image) }}" 
                                         style="width: 100%; height: 100%; object-fit: cover;" 
                                         onerror="this.src='{{ asset('backend/img/no-image.png') }}'">
                                </div>
                            </td>
                            <td><strong>{{ $pro->name }}</strong></td>
                            <td class="text-danger font-weight-bold">{{ number_format($pro->price) }} VNĐ</td>
                            <td>
                                <span class="badge {{ $pro->stock > 0 ? 'badge-success' : 'badge-danger' }}">
                                    {{ $pro->stock > 0 ? 'Còn hàng (' . $pro->stock . ')' : 'Hết hàng' }}
                                </span>
                            </td>
                            <td><small class="text-muted">{{ $pro->classification ?? '-' }}</small></td>
                            <td>
                                <div class="d-flex justify-content-center">
                                    <a href="{{ route('admin.product.edit', $pro->id) }}" class="btn btn-warning btn-sm shadow-sm mr-2" title="Sửa">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.product.destroy', $pro->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa sản phẩm này?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm shadow-sm" title="Xóa">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Không tìm thấy sản phẩm nào.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3 d-flex justify-content-center">
                {{ $products->links() }}
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addProductModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form action="{{ route('admin.product.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title font-weight-bold">Thêm sản phẩm mới</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label class="font-weight-bold">Tên sản phẩm <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" placeholder="Ví dụ: Tinh dầu Bưởi nguyên chất" required>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Giá bán (VNĐ)</label>
                                        <input type="number" name="price" class="form-control" placeholder="0" required min="0">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Số lượng kho</label>
                                        <input type="number" name="stock" class="form-control" value="1" min="0">
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-2">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Danh mục</label>
                                        <select name="category_id" class="form-control">
                                            <option value="">-- Chọn danh mục --</option>
                                            @foreach($categories as $cat)
                                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Phân loại</label>
                                        <select name="classification" class="form-control">
                                            <option value="">-- Không chọn --</option>
                                            <option value="Tinh dầu nguyên chất">Tinh dầu nguyên chất</option>
                                            <option value="Hương liệu pha">Hương liệu pha</option>
                                            <option value="Tinh dầu hỗn hợp (Blend Oil)">Tinh dầu hỗn hợp (Blend Oil)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold">Ảnh sản phẩm</label>
                                <div class="custom-file">
                                    <input type="file" name="image" class="custom-file-input" id="imgInputModal" accept="image/*">
                                    <label class="custom-file-label" for="imgInputModal">Chọn file</label>
                                </div>
                                <div class="mt-3 text-center border p-2 rounded bg-light">
                                    <img id="previewModal" src="{{ asset('backend/img/no-image.png') }}" 
                                         style="max-width: 100%; height: 150px; object-fit: contain;">
                                </div>
                                <small class="form-text text-muted">Dung lượng tối đa 2MB.</small>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="font-weight-bold">Mô tả sản phẩm</label>
                                <textarea name="description" class="form-control" rows="3" placeholder="Mô tả ngắn về sản phẩm..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary px-4 shadow">Lưu lại ngay</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    // Preview ảnh trước khi upload
    document.getElementById('imgInputModal')?.addEventListener('change', function(e) {
        const [file] = this.files;
        if (file) {
            // Hiển thị tên file lên label
            let fileName = file.name;
            let label = this.nextElementSibling;
            label.innerText = fileName;
            
            // Preview ảnh
            document.getElementById('previewModal').src = URL.createObjectURL(file);
        }
    });
</script>
@endsection