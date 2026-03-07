@extends('admin.layout.admin_layout')
@section('title', 'Quản lý Đánh giá khách hàng')

@section('content')
<style>
    /* Tổng thể & Thống kê */
    .card-custom { border-radius: 15px; border: none; }
    .stat-card { border-radius: 15px; border: none; transition: 0.3s; }
    .stat-card:hover { transform: translateY(-5px); }
    
    /* Table Styling */
    .table thead th { 
        background-color: #f8f9fc; 
        text-transform: uppercase; 
        font-size: 0.75rem; 
        letter-spacing: 0.5px; 
        border-bottom: 2px solid #ebedef; 
        padding: 15px 10px;
    }
    .table td { vertical-align: middle !important; padding: 15px 10px; }
    
    /* Hình ảnh & Media */
    .review-img {
        width: 60px; height: 60px; object-fit: cover;
        border-radius: 10px; transition: 0.3s;
        border: 2px solid #fff; box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        cursor: pointer;
    }
    .review-img:hover { transform: scale(1.1); z-index: 10; }

    /* Video Play Button Overlay */
    .video-container { position: relative; width: 60px; height: 60px; }
    .video-preview { width: 60px; height: 60px; object-fit: cover; border-radius: 10px; border: 2px solid #fff; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
    .play-overlay {
        position: absolute; top: 0; left: 0; width: 100%; height: 100%;
        display: flex; align-items: center; justify-content: center;
        background: rgba(0,0,0,0.2); border-radius: 10px; color: white; cursor: pointer;
    }

    /* Star Rating */
    .star-rating { color: #ffc107; font-size: 0.8rem; }
    
    /* Phần phản hồi */
    .reply-section {
        border-left: 4px solid #1cc88a; background: #f6fffb;
        padding: 12px; border-radius: 8px; margin-top: 10px; position: relative;
    }

    /* Tag Styling */
    .review-tag { font-size: 0.65rem; padding: 2px 8px; margin-right: 4px; margin-bottom: 4px; display: inline-block; border-radius: 50px; background: #eef2f7; color: #4e73df; font-weight: 600; }

    /* Nút bấm hiện đại */
    .btn-action {
        width: 35px; height: 35px; border-radius: 8px;
        display: inline-flex; align-items: center; justify-content: center;
        transition: 0.3s; border: none; color: white;
    }
    .btn-action:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.15); }
    .btn-pill { border-radius: 50px; font-weight: 600; font-size: 0.75rem; }

    /* Badge tùy chỉnh */
    .badge-soft-success { background: rgba(28, 200, 138, 0.1); color: #1cc88a; border: 1px solid rgba(28, 200, 138, 0.2); }
    .badge-soft-warning { background: rgba(246, 194, 62, 0.1); color: #f6c23e; border: 1px solid rgba(246, 194, 62, 0.2); }
</style>

<div class="container-fluid py-4">
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h1 class="h3 mb-0 text-gray-800 font-weight-bold">Quản lý Đánh giá</h1>
            <p class="text-muted small mb-0">Theo dõi và phản hồi trải nghiệm của khách hàng</p>
        </div>
        <div class="col-md-6 text-md-right mt-3 mt-md-0">
            <div class="d-inline-flex bg-white shadow-sm rounded-pill px-3 py-2">
                <div class="text-center px-3 border-right">
                    <div class="text-xs text-uppercase text-muted font-weight-bold">Trung bình</div>
                    <div class="h6 mb-0 font-weight-bold text-warning">{{ number_format($reviews->avg('rating'), 1) }} <i class="fas fa-star"></i></div>
                </div>
                <div class="text-center px-3">
                    <div class="text-xs text-uppercase text-muted font-weight-bold">Tổng số</div>
                    <div class="h6 mb-0 font-weight-bold text-primary">{{ $reviews->total() }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm card-custom">
        <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between border-bottom">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-comments mr-2"></i>Tất cả đánh giá</h6>
            <div class="dropdown no-arrow">
                <button class="btn btn-sm btn-light border rounded-pill px-3" type="button" id="filterMenu" data-toggle="dropdown">
                    <i class="fas fa-filter mr-1"></i> Bộ lọc
                </button>
                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                    <a class="dropdown-item" href="?rating=5">5 Sao</a>
                    <a class="dropdown-item" href="?rating=1">1 Sao (Cần chú ý)</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="{{ route('admin.reviews.index') }}">Xóa lọc</a>
                </div>
            </div>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="pl-4">Khách hàng</th>
                            <th>Sản phẩm</th>
                            <th>Đánh giá</th>
                            <th style="width: 35%;">Nội dung & Media</th>
                            <th class="text-center">Hiển thị</th>
                            <th class="text-right pr-4">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reviews as $review)
                        <tr>
                            <td class="pl-4">
                                <div class="d-flex align-items-center">
                                    <div class="mr-3 rounded-circle bg-gradient-primary d-flex align-items-center justify-content-center text-white font-weight-bold shadow-sm" style="width: 42px; height:42px;">
                                        {{ strtoupper(substr($review->user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="font-weight-bold text-dark mb-0">{{ $review->user->name }}</div>
                                        <small class="text-muted"><i class="far fa-clock mr-1"></i>{{ $review->created_at->diffForHumans() }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="small font-weight-bold text-truncate" style="max-width: 150px;">
                                    <a href="#" class="text-decoration-none text-primary">{{ $review->product->name }}</a>
                                </div>
                                <div class="text-muted" style="font-size: 0.7rem;">ID: #PRO-{{ $review->product->id }}</div>
                            </td>
                            <td>
                                <div class="star-rating mb-1">
                                    @for($i=1; $i<=5; $i++)
                                        <i class="{{ $i <= $review->rating ? 'fas' : 'far' }} fa-star"></i>
                                    @endfor
                                </div>
                                <span class="badge badge-pill {{ $review->is_resolved ? 'badge-soft-success' : 'badge-soft-warning' }}" style="font-size: 0.65rem;">
                                    {{ $review->is_resolved ? 'ĐÃ TRẢ LỜI' : 'CHƯA TRẢ LỜI' }}
                                </span>
                            </td>
                            <td>
                                @if($review->tags)
                                    <div class="mb-2">
                                        @foreach($review->tags as $tag)
                                            <span class="review-tag">#{{ $tag }}</span>
                                        @endforeach
                                    </div>
                                @endif

                                <div class="text-dark bg-light p-2 rounded mb-2 border-left border-primary small italic">
                                    "{{ $review->comment ?? 'Không có nội dung văn bản' }}"
                                </div>
                                
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                    {{-- Ảnh chính --}}
                                    @if($review->image)
                                        <img src="{{ Str::contains($review->image, 'http') ? $review->image : asset('storage/' . $review->image) }}" 
                                             class="review-img mr-2 mb-2" onclick="viewLargeImage(this.src)">
                                    @endif

                                    {{-- Video đánh giá --}}
                                    @if($review->video)
                                        <div class="video-container mr-2 mb-2" onclick="viewVideo('{{ $review->video }}')">
                                            <video class="video-preview">
                                                <source src="{{ $review->video }}" type="video/mp4">
                                            </video>
                                            <div class="play-overlay">
                                                <i class="fas fa-play fa-xs"></i>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                
                                @if($review->reply)
                                    <div class="reply-section shadow-sm" id="replyDisplay{{ $review->id }}">
                                        <div class="float-actions" style="position: absolute; top: 8px; right: 8px;">
                                            <button type="button" class="btn btn-xs btn-outline-primary border-0" onclick="toggleEdit('{{ $review->id }}')" title="Sửa">
                                                <i class="fas fa-edit text-xs"></i>
                                            </button>
                                            <button type="button" class="btn btn-xs btn-outline-danger border-0" onclick="confirmDeleteReply('{{ $review->id }}')" title="Xóa">
                                                <i class="fas fa-times text-xs"></i>
                                            </button>
                                        </div>
                                        <div class="small text-success font-weight-bold mb-1">
                                            <i class="fas fa-reply mr-1"></i>Phản hồi của bạn:
                                        </div>
                                        <p class="small mb-0 text-muted">"{{ $review->reply }}"</p>
                                    </div>

                                    <div class="collapse mt-2" id="editReplyForm{{ $review->id }}">
                                        <form action="{{ route('admin.reviews.update_reply', $review->id) }}" method="POST" class="bg-white p-3 border rounded shadow-sm">
                                            @csrf @method('PUT')
                                            <textarea name="reply" class="form-control mb-2 text-sm" rows="2" required>{{ $review->reply }}</textarea>
                                            <div class="text-right">
                                                <button type="button" class="btn btn-link btn-sm text-muted" onclick="toggleEdit('{{ $review->id }}')">Hủy</button>
                                                <button type="submit" class="btn btn-primary btn-sm btn-pill px-3">Cập nhật</button>
                                            </div>
                                        </form>
                                    </div>
                                @else
                                    <button class="btn btn-pill btn-outline-primary btn-sm mt-1" type="button" data-toggle="collapse" data-target="#replyForm{{ $review->id }}">
                                        <i class="fas fa-paper-plane mr-1"></i>Gửi phản hồi
                                    </button>
                                    <div class="collapse mt-3" id="replyForm{{ $review->id }}">
                                        <form action="{{ route('admin.reviews.reply', $review->id) }}" method="POST" class="p-3 border rounded-lg bg-white shadow-sm">
                                            @csrf
                                            <div class="form-group mb-2">
                                                <label class="small font-weight-bold text-primary">Nội dung trả lời:</label>
                                                <textarea name="reply" class="form-control border-0 bg-light text-sm" rows="2" placeholder="Cảm ơn khách hàng..."></textarea>
                                            </div>
                                            <div class="text-right">
                                                <button type="submit" class="btn btn-primary btn-sm btn-pill px-4">Gửi ngay</button>
                                            </div>
                                        </form>
                                    </div>
                                @endif
                            </td>
                            <td class="text-center">
                                <form action="{{ route('admin.reviews.toggle', $review->id) }}" method="POST" id="toggle-form-{{ $review->id }}">
                                    @csrf
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="switch{{ $review->id }}" 
                                            {{ $review->status == 'active' ? 'checked' : '' }} 
                                            onchange="document.getElementById('toggle-form-{{ $review->id }}').submit()">
                                        <label class="custom-control-label" for="switch{{ $review->id }}"></label>
                                    </div>
                                </form>
                            </td>
                            <td class="text-right pr-4">
                                <button type="button" class="btn-action bg-danger shadow-sm" 
                                        onclick="confirmDeleteReview('{{ $review->id }}')" title="Xóa đánh giá">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                                
                                <form action="{{ route('admin.reviews.destroy', $review->id) }}" method="POST" id="delete-review-form-{{ $review->id }}" class="d-none">
                                    @csrf @method('DELETE')
                                </form>
                                <form action="{{ route('admin.reviews.delete_reply', $review->id) }}" method="POST" id="delete-reply-form-{{ $review->id }}" class="d-none">
                                    @csrf @method('DELETE')
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <img src="https://illustrations.popsy.co/gray/box-empty.svg" style="width: 150px;" class="mb-3">
                                <p class="text-muted">Chưa có đánh giá nào để hiển thị.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($reviews->hasPages())
            <div class="card-footer bg-white border-0 py-4">
                <div class="d-flex justify-content-center">
                    {{ $reviews->links('pagination::bootstrap-4') }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Phóng to ảnh khi click
    function viewLargeImage(url) {
        Swal.fire({
            imageUrl: url,
            imageAlt: 'Review image',
            showCloseButton: true,
            showConfirmButton: false,
            customClass: { image: 'rounded-lg shadow-lg' }
        });
    }

    // Xem Video bằng SweetAlert2
    function viewVideo(url) {
        Swal.fire({
            html: `
                <video width="100%" controls autoplay style="border-radius: 15px; outline: none;">
                    <source src="${url}" type="video/mp4">
                    Trình duyệt của bạn không hỗ trợ phát video.
                </video>
            `,
            showCloseButton: true,
            showConfirmButton: false,
            background: 'transparent',
            customClass: { popup: 'bg-transparent shadow-none' }
        });
    }

    // Đóng/mở chỉnh sửa
    function toggleEdit(id) {
        $(`#replyDisplay${id}`).slideToggle(200);
        $(`#editReplyForm${id}`).collapse('toggle');
    }

    // Xóa phản hồi
    function confirmDeleteReply(id) {
        Swal.fire({
            title: 'Xóa phản hồi?',
            text: "Đánh giá này sẽ chuyển về trạng thái 'Chưa trả lời'.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#4e73df',
            confirmButtonText: 'Đồng ý',
            cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) document.getElementById('delete-reply-form-' + id).submit();
        });
    }

    // Xóa Review
    function confirmDeleteReview(id) {
        Swal.fire({
            title: 'Xóa vĩnh viễn?',
            text: "Nội dung này bao gồm cả hình ảnh/video sẽ không thể khôi phục!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e74a3b',
            confirmButtonText: 'Xác nhận xóa',
            cancelButtonText: 'Quay lại'
        }).then((result) => {
            if (result.isConfirmed) document.getElementById('delete-review-form-' + id).submit();
        });
    }

    // Toast Thông báo
    $(document).ready(function() {
        const Toast = Swal.mixin({
            toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true
        });

        @if(session('success')) Toast.fire({ icon: 'success', title: "{{ session('success') }}" }); @endif
        @if(session('error')) Toast.fire({ icon: 'error', title: "{{ session('error') }}" }); @endif
    });
</script>
@endsection