@extends('admin.layout.admin_layout')

@section('title', 'Chỉnh sửa: ' . $product->name)

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Cập nhật sản phẩm</h1>
        <a href="{{ route('admin.product.index') }}" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Quay lại danh sách
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-white">
            <h6 class="m-0 font-weight-bold text-primary">Thông tin chi tiết: <span class="text-dark">{{ $product->name }}</span></h6>
        </div>
        <div class="card-body">
            {{-- Hiển thị thông báo lỗi nếu có --}}
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
                            <input type="text" name="name" class="form-control" value="{{ old('name', $product->name) }}" required>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label class="font-weight-bold">Giá bán (VNĐ) <span class="text-danger">*</span></label>
                                <input type="number" name="price" class="form-control" value="{{ old('price', $product->price) }}" required min="0">
                            </div>
                            <div class="form-group col-md-6">
                                <label class="font-weight-bold">Số lượng trong kho</label>
                                <input type="number" name="stock" class="form-control" value="{{ old('stock', $product->stock) }}" min="0">
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
                                <label class="font-weight-bold">Phân loại</label>
                                <select name="classification" class="form-control">
                                    <option value="">-- Không chọn --</option>
                                    <option value="Tinh dầu nguyên chất" {{ old('classification', $product->classification) == 'Tinh dầu nguyên chất' ? 'selected' : '' }}>Tinh dầu nguyên chất</option>
                                    <option value="Hương liệu pha" {{ old('classification', $product->classification) == 'Hương liệu pha' ? 'selected' : '' }}>Hương liệu pha</option>
                                    <option value="Tinh dầu hỗn hợp (Blend Oil)" {{ old('classification', $product->classification) == 'Tinh dầu hỗn hợp (Blend Oil)' ? 'selected' : '' }}>Tinh dầu hỗn hợp (Blend Oil)</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Mô tả sản phẩm</label>
                            <textarea name="description" class="form-control" rows="5">{{ old('description', $product->description) }}</textarea>
                        </div>
                    </div>

                    <div class="col-md-4 border-left">
                        <div class="form-group">
                            <label class="font-weight-bold text-primary">Ảnh sản phẩm hiện tại</label>
                            
                            <div class="text-center p-3 border rounded bg-light mb-3" style="min-height: 250px; display: flex; align-items: center; justify-content: center;">
                                @php
                                    // Logic xử lý đường dẫn ảnh thông minh
                                    $currentImageUrl = asset('backend/img/no-image.png');
                                    if ($product->image) {
                                        if (filter_var($product->image, FILTER_VALIDATE_URL)) {
                                            // Nếu là link Cloudinary thì dùng trực tiếp và thêm timestamp để tránh cache
                                            $currentImageUrl = $product->image . '?v=' . time();
                                        } else {
                                            // Nếu là ảnh local, dọn dẹp đường dẫn để tránh bị lặp 'uploads/product/'
                                            $cleanPath = str_replace(['public/', 'uploads/product/'], '', $product->image);
                                            $currentImageUrl = asset('uploads/product/' . $cleanPath) . '?v=' . time();
                                        }
                                    }
                                @endphp
                                <img id="previewEdit" 
                                     src="{{ $currentImageUrl }}" 
                                     style="max-width: 100%; max-height: 250px; object-fit: contain; border-radius: 8px;"
                                     onerror="this.onerror=null;this.src='{{ asset('backend/img/no-image.png') }}'">
                            </div>

                            <div class="custom-file">
                                <input type="file" name="image" class="custom-file-input" id="imgInputEdit" accept="image/*">
                                <label class="custom-file-label text-truncate" for="imgInputEdit">Thay đổi ảnh...</label>
                            </div>
                            <small class="form-text text-muted mt-2 text-center italic text-danger">Lưu ý: Chỉ chọn ảnh nếu bạn muốn thay đổi ảnh cũ.</small>
                        </div>
                    </div>
                </div>

                <hr class="mt-4">

                <div class="form-group d-flex justify-content-end mb-0">
                    <a href="{{ route('admin.product.index') }}" class="btn btn-light mr-2 border">Hủy bỏ</a>
                    <button type="submit" class="btn btn-primary px-5 shadow">
                        <i class="fas fa-save mr-1"></i> Cập nhật ngay
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
            const [file] = this.files;
            if (file) {
                // Hiển thị tên file lên nhãn
                let label = this.nextElementSibling;
                label.innerText = file.name;

                // Cập nhật ảnh xem trước (Preview)
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    });
</script>
@endsection