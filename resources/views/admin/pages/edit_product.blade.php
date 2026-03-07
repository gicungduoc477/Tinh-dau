@extends('admin.layout.admin_layout')

@section('title', 'Chỉnh sửa: ' . $product->name)

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Cập nhật sản phẩm</h1>
        <a href="{{ route('admin.product.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Quay lại danh sách
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Thông tin chi tiết sản phẩm: {{ $product->name }}</h6>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <form action="{{ route('admin.product.update', $product->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label class="font-weight-bold">Tên sản phẩm <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $product->name) }}" placeholder="Nhập tên sản phẩm..." required>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label class="font-weight-bold">Giá bán (VNĐ) <span class="text-danger">*</span></label>
                                <input type="number" name="price" class="form-control" value="{{ old('price', $product->price) }}" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label class="font-weight-bold">Số lượng trong kho</label>
                                <input type="number" name="stock" class="form-control" value="{{ old('stock', $product->stock) }}">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label class="font-weight-bold">Danh mục sản phẩm</label>
                                <select name="category_id" class="form-control">
                                    <option value="">-- Chọn danh mục --</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label class="font-weight-bold">Phân loại loại hình</label>
                                <select name="classification" class="form-control">
                                    <option value="">-- Không chọn --</option>
                                    <option value="Tinh dầu nguyên chất" {{ old('classification', $product->classification) == 'Tinh dầu nguyên chất' ? 'selected' : '' }}>Tinh dầu nguyên chất</option>
                                    <option value="Tinh dầu không nguyên chất" {{ old('classification', $product->classification) == 'Tinh dầu không nguyên chất' ? 'selected' : '' }}>Tinh dầu không nguyên chất</option>
                                    <option value="Tinh dầu hỗn hợp (Blend Oil)" {{ old('classification', $product->classification) == 'Tinh dầu hỗn hợp (Blend Oil)' ? 'selected' : '' }}>Tinh dầu hỗn hợp (Blend Oil)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 border-left">
                        <div class="form-group">
                            <label class="font-weight-bold">Ảnh sản phẩm hiện tại</label>
                            <div class="custom-file mb-3">
                                <input type="file" name="image" class="custom-file-input" id="imgInputEdit" accept="image/*">
                                <label class="custom-file-label" for="imgInputEdit text-truncate">Chọn ảnh mới...</label>
                            </div>
                            
                            <div class="text-center p-3 border rounded bg-light" style="min-height: 250px; display: flex; align-items: center; justify-content: center;">
                                <img id="previewEdit" 
                                     src="{{ $product->image ? asset('uploads/product/'.$product->image) : asset('backend/img/no-image.png') }}" 
                                     style="max-width: 100%; max-height: 220px; object-fit: contain; border-radius: 5px; shadow: 0 0 5px rgba(0,0,0,0.1);">
                            </div>
                            <small class="form-text text-muted text-center mt-2">Ảnh xem trước (Nên chọn ảnh vuông 1:1)</small>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="form-group mt-4 d-flex justify-content-end">
                    <button type="reset" class="btn btn-light mr-2 border">Hủy thay đổi</button>
                    <button type="submit" class="btn btn-primary px-5 shadow-sm">
                        <i class="fas fa-save mr-1"></i> Lưu thay đổi ngay
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const imgInput = document.getElementById('imgInputEdit');
        const preview = document.getElementById('previewEdit');

        imgInput.addEventListener('change', function(e) {
            // 1. Xử lý xem trước ảnh
            const [file] = this.files;
            if (file) {
                preview.src = URL.createObjectURL(file);
                
                // 2. Cập nhật tên file vào nhãn (label) của Bootstrap
                let fileName = e.target.files[0].name;
                let nextSibling = e.target.nextElementSibling;
                nextSibling.innerText = fileName;
            }
        });
    });
</script>
@endsection