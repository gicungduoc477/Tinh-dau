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
                    <input type="search" class="form-control bg-white border-0 small" name="q" 
                           placeholder="Tìm theo tên..." value="{{ request('q') }}">
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-search fa-sm"></i>
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
        <div class="alert alert-success alert-dismissible fade show border-left-success" role="alert">
            <i class="fas fa-check-circle mr-2"></i>
            <strong>Thành công!</strong> {{ session('message') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-light">
            <h6 class="m-0 font-weight-bold text-primary">Danh sách sản phẩm hiện có</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr class="text-center">
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
                            <td class="text-center align-middle">{{ $products->firstItem() + $key }}</td>
                            <td class="text-center align-middle">
                                <div style="width: 60px; height: 60px; margin: 0 auto; overflow: hidden; background: #f8f9fc; border: 1px solid #e3e6f0; border-radius: 5px;">
                                    @php
                                        $imagePath = $pro->image;
                                        $finalUrl = asset('backend/img/no-image.png');

                                        if ($imagePath) {
                                            if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
                                                $finalUrl = $imagePath . '?v=' . time();
                                            } else {
                                                $cleanPath = str_replace(['public/', 'uploads/product/'], '', $imagePath);
                                                $finalUrl = asset('uploads/product/' . $cleanPath) . '?v=' . time();
                                            }
                                        }
                                    @endphp
                                    <img src="{{ $finalUrl }}" 
                                         style="width: 100%; height: 100%; object-fit: cover;" 
                                         onerror="this.onerror=null;this.src='{{ asset('backend/img/no-image.png') }}'">
                                </div>
                            </td>
                            <td class="align-middle"><strong>{{ $pro->name }}</strong></td>
                            <td class="align-middle text-danger font-weight-bold">{{ number_format($pro->price) }} VNĐ</td>
                            <td class="align-middle text-center">
                                <span class="badge {{ $pro->stock > 0 ? 'badge-success' : 'badge-danger' }} p-2">
                                    {{ $pro->stock > 0 ? 'Còn hàng (' . $pro->stock . ')' : 'Hết hàng' }}
                                </span>
                            </td>
                            <td class="align-middle"><small class="text-muted">{{ $pro->classification ?? '-' }}</small></td>
                            <td class="align-middle text-center">
                                <div class="d-flex justify-content-center">
                                    <a href="{{ route('admin.product.edit', $pro->id) }}" class="btn btn-warning btn-sm shadow-sm mr-2">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.product.destroy', $pro->id) }}" method="POST" onsubmit="return confirm('Xóa sản phẩm này?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm shadow-sm">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center py-5">Trống</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3 d-flex justify-content-center">
                {{ $products->appends(request()->input())->links() }}
            </div>
        </div>
    </div>
</div>

{{-- Modal Thêm Sản Phẩm - Quan trọng nhất là enctype --}}
<div class="modal fade" id="addProductModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form action="{{ route('admin.product.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Thêm sản phẩm mới</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Tên sản phẩm <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Giá bán</label>
                                        <input type="number" name="price" class="form-control" required min="0">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Kho</label>
                                        <input type="number" name="stock" class="form-control" value="1">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Danh mục</label>
                                        <select name="category_id" class="form-control">
                                            @foreach($categories as $cat)
                                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Phân loại</label>
                                        <select name="classification" class="form-control">
                                            <option value="Tinh dầu nguyên chất">Tinh dầu nguyên chất</option>
                                            <option value="Hương liệu pha">Hương liệu pha</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Ảnh sản phẩm</label>
                                <div class="custom-file">
                                    <input type="file" name="image" class="custom-file-input" id="imgInputModal" accept="image/*">
                                    <label class="custom-file-label">Chọn file...</label>
                                </div>
                                <div class="mt-3 text-center border p-2">
                                    <img id="previewModal" src="{{ asset('backend/img/no-image.png') }}" style="max-width: 100%; height: 150px; object-fit: contain;">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Mô tả</label>
                                <textarea name="description" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Lưu sản phẩm</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    document.getElementById('imgInputModal')?.addEventListener('change', function(e) {
        const file = this.files[0];
        if (file) {
            this.nextElementSibling.innerText = file.name;
            const reader = new FileReader();
            reader.onload = (e) => document.getElementById('previewModal').src = e.target.result;
            reader.readAsDataURL(file);
        }
    });
</script>
@endsection