@extends('layouts.app')

@section('title', 'Viết đánh giá cho ' . $product->name)

@section('content')
<style>
    .review-container { max-width: 800px; margin: 40px auto; }
    .product-preview { background: #f9f9f9; border-radius: 15px; padding: 20px; border: 1px solid #eee; }
    
    /* Star Rating System */
    .rating-group { display: flex; flex-direction: row-reverse; justify-content: center; gap: 10px; }
    .rating-group input { display: none; }
    .rating-group label { cursor: pointer; font-size: 2.5rem; color: #ddd; transition: 0.3s; }
    .rating-group input:checked ~ label { color: #ffc107; }
    .rating-group label:hover, .rating-group label:hover ~ label { color: #ffe082; }

    /* Tag Selector */
    .tag-item { cursor: pointer; border-radius: 50px; padding: 6px 15px; border: 1px solid #ddd; transition: 0.3s; display: inline-block; margin: 5px; font-size: 0.9rem; }
    .tag-checkbox:checked + .tag-item { background: #27ae60; color: white; border-color: #27ae60; }
    .tag-checkbox { display: none; }

    /* Media Upload Styles */
    .media-upload-wrapper {
        border: 2px dashed #ddd; border-radius: 12px; padding: 20px; text-align: center;
        position: relative; cursor: pointer; transition: 0.3s; min-height: 180px;
        display: flex; flex-direction: column; align-items: center; justify-content: center;
    }
    .media-upload-wrapper:hover { border-color: #27ae60; background: #f0fff4; }
    
    #preview-container { max-width: 100%; display: none; margin-top: 15px; }
    #image-preview, #video-preview { 
        max-width: 300px; max-height: 200px; border-radius: 8px; 
        box-shadow: 0 4px 10px rgba(0,0,0,0.1); display: none; margin: 0 auto;
    }

    .btn-submit { 
        background: #27ae60; color: white; border-radius: 50px; padding: 12px 60px; 
        font-weight: 700; border: none; transition: 0.3s; 
    }
    .btn-submit:hover { background: #219150; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(39,174,96,0.3); }

    .animate-in { animation: fadeInUp 0.5s ease-out; }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
</style>

<div class="container">
    <div class="review-container animate-in">
        <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5">
            <h2 class="fw-bold text-center mb-4">Đánh giá sản phẩm</h2>
            
            <div class="product-preview d-flex align-items-center mb-4">
                {{-- Logic FIX lỗi hiện ảnh --}}
                @php
                    $pImg = trim($product->image ?? '');
                    if (filter_var($pImg, FILTER_VALIDATE_URL)) {
                        $displayUrl = $pImg;
                    } elseif (!empty($pImg) && !str_contains($pImg, '/')) {
                        // Nếu là tên file thô, ưu tiên tìm trong uploads/product/
                        $displayUrl = asset('uploads/product/' . $pImg);
                    } elseif (!empty($pImg)) {
                        // Nếu có đường dẫn /storage/... hoặc đường dẫn tương đối khác
                        $displayUrl = asset(ltrim($pImg, '/'));
                    } else {
                        $displayUrl = asset('backend/img/no-image.png');
                    }
                @endphp

                <img src="{{ $displayUrl }}" 
                     alt="{{ $product->name }}"
                     class="rounded-3 me-3 shadow-sm" 
                     style="width: 100px; height: 100px; object-fit: cover; border: 2px solid #fff;"
                     onerror="this.onerror=null; this.src='{{ asset('backend/img/no-image.png') }}';">

                <div>
                    <h5 class="fw-bold mb-1">{{ $product->name }}</h5>
                    <p class="text-muted small mb-0"><i class="bi bi-tag-fill me-1"></i> Phân loại: {{ $product->classification ?? 'Mặc định' }}</p>
                </div>
            </div>

            <form action="{{ route('reviews.store') }}" method="POST" enctype="multipart/form-data" id="review-form">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">

                {{-- Chấm điểm sao --}}
                <div class="text-center mb-4">
                    <label class="fw-bold d-block mb-2">Bạn thấy sản phẩm này thế nào?</label>
                    <div class="rating-group">
                        @for($i=5; $i>=1; $i--)
                            <input type="radio" id="star{{$i}}" name="rating" value="{{$i}}" {{ old('rating') == $i ? 'checked' : '' }} required>
                            <label for="star{{$i}}"><i class="bi bi-star-fill"></i></label>
                        @endfor
                    </div>
                </div>

                {{-- Tag chọn nhanh --}}
                <div class="mb-4 text-center">
                    <label class="fw-bold mb-2 d-block">Cảm nhận nhanh:</label>
                    <div class="tags-container">
                        @php $tags = ['Giao hàng nhanh', 'Đóng gói chắc chắn', 'Chất lượng tuyệt vời', 'Giá cả hợp lý', 'Phục vụ tận tâm']; @endphp
                        @foreach($tags as $tag)
                            <input type="checkbox" name="tags[]" value="{{ $tag }}" id="tag-{{ $loop->index }}" class="tag-checkbox">
                            <label for="tag-{{ $loop->index }}" class="tag-item">{{ $tag }}</label>
                        @endforeach
                    </div>
                </div>

                {{-- Viết bình luận --}}
                <div class="mb-4">
                    <label class="fw-bold mb-2">Nhận xét chi tiết:</label>
                    <textarea name="comment" rows="4" 
                              class="form-control rounded-3 p-3 border-light-subtle shadow-none" 
                              placeholder="Chia sẻ trải nghiệm của bạn về sản phẩm...">{{ old('comment') }}</textarea>
                    <small class="text-muted">Đánh giá 5 sao không kèm bình luận sẽ được phản hồi tự động.</small>
                </div>

                {{-- Upload Media (Ảnh/Video) --}}
                <div class="mb-4">
                    <label class="fw-bold mb-2">Ảnh hoặc Video thực tế:</label>
                    <div class="media-upload-wrapper" id="drop-zone" onclick="document.getElementById('media-input').click()">
                        <div id="upload-placeholder">
                            <i class="bi bi-camera-reels-fill fs-1 text-muted"></i>
                            <p class="mb-0 text-muted fw-bold">Thêm hình ảnh hoặc video</p>
                            <small class="text-muted text-uppercase">(Tối đa 20MB)</small>
                        </div>
                        
                        <div id="preview-container">
                            <img id="image-preview" src="#">
                            <video id="video-preview" controls></video>
                            <div id="remove-media" class="mt-3 text-danger small fw-bold" style="cursor: pointer;">
                                <i class="bi bi-trash3-fill"></i> Xóa và chọn lại
                            </div>
                        </div>
                        
                        <input type="file" name="image" id="media-input" hidden accept="image/*,video/*">
                    </div>
                </div>

                <div class="text-center mt-5">
                    <button type="submit" class="btn btn-submit shadow" id="submit-btn">
                        <i class="bi bi-send-check-fill me-2"></i> Gửi đánh giá ngay
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const mediaInput = document.getElementById('media-input');
    const imagePreview = document.getElementById('image-preview');
    const videoPreview = document.getElementById('video-preview');
    const previewContainer = document.getElementById('preview-container');
    const uploadPlaceholder = document.getElementById('upload-placeholder');
    const removeBtn = document.getElementById('remove-media');
    const reviewForm = document.getElementById('review-form');
    const submitBtn = document.getElementById('submit-btn');

    mediaInput.addEventListener('change', function(event) {
        const file = event.target.files[0];
        if (file) {
            if (file.size > 20 * 1024 * 1024) {
                alert("File quá lớn! Vui lòng chọn tệp dưới 20MB.");
                this.value = ""; return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                uploadPlaceholder.style.display = 'none';
                previewContainer.style.display = 'block';

                if (file.type.startsWith('video/')) {
                    videoPreview.src = e.target.result;
                    videoPreview.style.display = 'block';
                    imagePreview.style.display = 'none';
                } else {
                    imagePreview.src = e.target.result;
                    imagePreview.style.display = 'block';
                    videoPreview.style.display = 'none';
                }
            }
            reader.readAsDataURL(file);
        }
    });

    removeBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        mediaInput.value = "";
        previewContainer.style.display = 'none';
        uploadPlaceholder.style.display = 'flex';
        videoPreview.src = "";
        imagePreview.src = "#";
    });

    reviewForm.addEventListener('submit', function() {
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Đang xử lý...';
        submitBtn.disabled = true;
    });
</script>
@endsection