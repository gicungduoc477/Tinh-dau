@extends('admin.layout.admin_layout')
@section('title', 'Quản lý Đánh giá khách hàng')

@section('content')
<style>
    /* Tổng thể & Thống kê */
    .card-custom { border-radius: 15px; border: none; overflow: hidden; }
    .stat-card { border-radius: 15px; border: none; transition: 0.3s; }
    .stat-card:hover { transform: translateY(-5px); }
    
    /* Table Styling */
    .table thead th { 
        background-color: #f8f9fc; 
        text-transform: uppercase; 
        font-size: 0.7rem; 
        letter-spacing: 0.5px; 
        border-bottom: 2px solid #ebedef; 
        padding: 15px 10px;
        color: #4e73df;
    }
    .table td { vertical-align: middle !important; padding: 15px 10px; border-bottom: 1px solid #f1f1f1; }
    .table-hover tbody tr:hover { background-color: #fcfdfe; }
    
    /* Hình ảnh & Media */
    .review-img {
        width: 65px; height: 65px; object-fit: cover;
        border-radius: 10px; transition: 0.3s;
        border: 2px solid #fff; box-shadow: 0 3px 8px rgba(0,0,0,0.1);
        cursor: pointer;
    }
    .review-img:hover { transform: scale(1.1); z-index: 10; box-shadow: 0 5px 15px rgba(0,0,0,0.2); }

    /* Video Play Button Overlay */
    .video-container { position: relative; width: 65px; height: 65px; }
    .video-preview { width: 65px; height: 65px; object-fit: cover; border-radius: 10px; border: 2px solid #fff; box-shadow: 0 3px 8px rgba(0,0,0,0.1); }
    .play-overlay {
        position: absolute; top: 0; left: 0; width: 100%; height: 100%;
        display: flex; align-items: center; justify-content: center;
        background: rgba(0,0,0,0.3); border-radius: 10px; color: white; cursor: pointer;
    }

    /* Star Rating */
    .star-rating { color: #ffc107; font-size: 0.8rem; }
    
    /* Phần phản hồi */
    .reply-section {
        border-left: 4px solid #1cc88a; background: #f8fffb;
        padding: 15px; border-radius: 10px; margin-top: 10px; position: relative;
    }

    /* Tag Styling */
    .review-tag { font-size: 0.7rem; padding: 4px 10px; margin-right: 5px; margin-bottom: 5px; display: inline-block; border-radius: 50px; background: #e8f0fe; color: #1a73e8; font-weight: 600; border: 1px solid #d2e3fc; }

    /* Nút bấm hiện đại */
    .btn-action {
        width: 35px; height: 35px; border-radius: 10px;
        display: inline-flex; align-items: center; justify-content: center;
        transition: 0.3s; border: none; color: white;
    }
    .btn-action:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.15); }
    .btn-pill { border-radius: 50px; font-weight: 600; font-size: 0.75rem; letter-spacing: 0.3px; }

    /* Badge tùy chỉnh */
    .badge-soft-success { background: #d4edda; color: #155724; padding: 5px 10px; }
    .badge-soft-warning { background: #fff3cd; color: #856404; padding: 5px 10px; }
    
    /* Comment box */
    .comment-text { font-size: 0.9rem; color: #2d3436; line-height: 1.5; }
    .empty-comment { color: #b2bec3; font-style: italic; font-size: 0.85rem; }
</style>

<div class="container-fluid py-4">
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h1 class="h3 mb-1 text-gray-800 font-weight-bold">Quản lý Đánh giá</h1>
            <p class="text-muted small mb-0">Lắng nghe ý kiến khách hàng để cải thiện dịch vụ</p>
        </div>
        <div class="col-md-6 text-md-right mt-3 mt-md-0">
            <div class="d-inline-flex bg-white shadow-sm rounded-pill px-4 py-2 border">
                <div class="text-center px-3 border-right">
                    <div class="text-xs text-uppercase text-muted font-weight-bold mb-1">Đánh giá TB</div>
                    <div class="h6 mb-0 font-weight-bold text-warning">{{ number_format($reviews->avg('rating'), 1) }} <i class="fas fa-star shadow-sm"></i></div>
                </div>
                <div class="text-center px-3">
                    <div class="text-xs text-uppercase text-muted font-weight-bold mb-1">Tổng lượt</div>
                    <div class="h6 mb-0 font-weight-bold text-primary">{{ $reviews->total() }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm card-custom">
        <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between border-bottom">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-comments mr-2"></i>Danh sách phản hồi</h6>
            <div class="dropdown no-arrow">
                <button class="btn btn-sm btn-white border rounded-pill px-3 shadow-sm" type="button" id="filterMenu" data-toggle="dropdown">
                    <i class="fas fa-filter mr-1 text-primary"></i> Lọc dữ liệu
                </button>
                <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in">
                    <a class="dropdown-item" href="?rating=5">5 Sao (Tuyệt vời)</a>
                    <a class="dropdown-item" href="?rating=1">1 Sao (Cần xử lý gấp)</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item text-danger" href="{{ route('admin.reviews.index') }}">Xóa tất cả bộ lọc</a>
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
                            <th>Xếp hạng</th>
                            <th style="width: 40%;">Nội dung đánh giá</th>
                            <th class="text-center">Ẩn/Hiện</th>
                            <th class="text-right pr-4">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reviews as $review)
                        <tr>
                            <td class="pl-4">
                                <div class="d-flex align-items-center">
                                    <div class="mr-3 rounded-circle bg-gradient-primary d-flex align-items-center justify-content-center text-white font-weight-bold shadow" style="width: 42px; height:42px; font-size: 1.1rem;">
                                        {{ strtoupper(substr($review->user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="font-weight-bold text-dark mb-0">{{ $review->user->name }}</div>
                                        <small class="text-muted"><i class="far fa-clock mr-1"></i>{{ $review->created_at->format('d/m/Y H:i') }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="small font-weight-bold text-truncate" style="max-width: 150px;">
                                    <a href="#" class="text-primary">{{ $review->product->name }}</a>
                                </div>
                                <div class="text-muted" style="font-size: 0.65rem;">ID: #PRO-{{ $review->product->id }}</div>
                            </td>
                            <td>
                                <div class="star-rating mb-1">
                                    @for($i=1; $i<=5; $i++)
                                        <i class="{{ $i <= $review->rating ? 'fas' : 'far' }} fa-star"></i>
                                    @endfor
                                </div>
                                <span class="badge badge-pill {{ $review->is_resolved ? 'badge-soft-success' : 'badge-soft-warning' }}" style="font-size: 0.6rem; font-weight: 700;">
                                    {{ $review->is_resolved ? 'ĐÃ TRẢ LỜI' : 'CHỜ PHẢN HỒI' }}
                                </span>
                            </td>
                            <td>
                                {{-- Hiển thị Tags (Câu trả lời có sẵn) --}}
                                @if($review->tags && count($review->tags) > 0)
                                    <div class="mb-2">
                                        @foreach($review->tags as $tag)
                                            <span class="review-tag">#{{ $tag }}</span>
                                        @endforeach
                                    </div>
                                @endif

                                {{-- Nội dung bình luận --}}
                                <div class="comment-text mb-3">
                                    @if($review->comment)
                                        <span class="text-dark font-italic">"{{ $review->comment }}"</span>
                                    @else
                                        <span class="empty-comment">(Khách hàng không để lại bình luận văn bản)</span>
                                    @endif
                                </div>
                                
                                {{-- Media: Ảnh & Video --}}
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                    @if($review->image)
                                        @php
                                            $imgUrl = Str::contains($review->image, 'http') ? $review->image : asset('storage/' . $review->image);
                                        @endphp
                                        <img src="{{ $imgUrl }}" class="review-img mr-2 mb-2" onclick="viewLargeImage('{{ $imgUrl }}')">
                                    @endif

                                    @if($review->video)
                                        @php
                                            $videoUrl = Str::contains($review->video, 'http') ? $review->video : asset('storage/' . $review->video);
                                        @endphp
                                        <div class="video-container mr-2 mb-2" onclick="viewVideo('{{ $videoUrl }}')">
                                            <video class="video-preview"><source src="{{ $videoUrl }}"></video>
                                            <div class="play-overlay"><i class="fas fa-play fa-sm"></i></div>
                                        </div>
                                    @endif
                                </div>
                                
                                {{-- Khu vực Phản hồi --}}
                                @if($review->reply)
                                    <div class="reply-section shadow-sm border" id="replyDisplay{{ $review->id }}">
                                        <div style="position: absolute; top: 10px; right: 10px;">
                                            <button type="button" class="btn btn-sm btn-link text-primary p-0 mr-2" onclick="toggleEdit('{{ $review->id }}')"><i class="fas fa-edit"></i></button>
                                            <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="confirmDeleteReply('{{ $review->id }}')"><i class="fas fa-trash-alt"></i></button>
                                        </div>
                                        <div class="small text-success font-weight-bold mb-1">
                                            <i class="fas fa-check-circle mr-1"></i>NATURESHOP ĐÃ PHẢN HỒI:
                                        </div>
                                        <p class="small mb-0 text-dark font-weight-500">"{{ $review->reply }}"</p>
                                    </div>

                                    <div class="collapse mt-2" id="editReplyForm{{ $review->id }}">
                                        <form action="{{ route('admin.reviews.update_reply', $review->id) }}" method="POST" class="bg-white p-3 border rounded shadow-sm">
                                            @csrf @method('PUT')
                                            <textarea name="reply" class="form-control mb-2 text-sm" rows="3" required>{{ $review->reply }}</textarea>
                                            <div class="text-right">
                                                <button type="button" class="btn btn-sm text-muted mr-2" onclick="toggleEdit('{{ $review->id }}')">Đóng</button>
                                                <button type="submit" class="btn btn-primary btn-sm btn-pill px-3 shadow-sm">Cập nhật phản hồi</button>
                                            </div>
                                        </form>
                                    </div>
                                @else
                                    <button class="btn btn-pill btn-outline-primary btn-sm mt-1" type="button" data-toggle="collapse" data-target="#replyForm{{ $review->id }}">
                                        <i class="fas fa-reply mr-1"></i> Viết câu trả lời
                                    </button>
                                    <div class="collapse mt-3" id="replyForm{{ $review->id }}">
                                        <form action="{{ route('admin.reviews.reply', $review->id) }}" method="POST" class="p-3 border rounded-lg bg-light shadow-sm">
                                            @csrf
                                            <div class="form-group mb-2">
                                                <label class="small font-weight-bold text-primary">Nội dung gửi đến khách hàng:</label>
                                                <textarea name="reply" class="form-control border-0 bg-white text-sm" rows="3" placeholder="Ví dụ: Cảm ơn bạn đã tin dùng sản phẩm của NatureShop..."></textarea>
                                            </div>
                                            <div class="text-right">
                                                <button type="submit" class="btn btn-primary btn-sm btn-pill px-4 shadow-sm">Gửi phản hồi</button>
                                            </div>
                                        </form>
                                    </div>
                                @endif
                            </td>
                            <td class="text-center">
                                <form action="{{ route('admin.reviews.toggle', $review->id) }}" method="POST" id="toggle-form-{{ $review->id }}">
                                    @csrf
                                    <div class="custom-control custom-switch custom-switch-md">
                                        <input type="checkbox" class="custom-control-input" id="switch{{ $review->id }}" 
                                            {{ $review->status == 'active' ? 'checked' : '' }} 
                                            onchange="document.getElementById('toggle-form-{{ $review->id }}').submit()">
                                        <label class="custom-control-label" for="switch{{ $review->id }}"></label>
                                    </div>
                                </form>
                            </td>
                            <td class="text-right pr-4">
                                <button type="button" class="btn-action bg-gradient-danger shadow-sm" 
                                        onclick="confirmDeleteReview('{{ $review->id }}')" title="Xóa đánh giá">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                                
                                <form action="{{ route('admin.reviews.destroy', $review->id) }}" method="POST" id="delete-review-form-{{ $review->id }}" class="d-none">@csrf @method('DELETE')</form>
                                <form action="{{ route('admin.reviews.delete_reply', $review->id) }}" method="POST" id="delete-reply-form-{{ $review->id }}" class="d-none">@csrf @method('DELETE')</form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <img src="https://illustrations.popsy.co/gray/box-empty.svg" style="width: 180px;" class="mb-3 opacity-50">
                                <h5 class="text-muted">Không tìm thấy đánh giá nào</h5>
                                <p class="small text-muted">Hãy thử thay đổi bộ lọc hoặc đợi phản hồi từ khách hàng.</p>
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

{{-- SweetAlert2 & Scripts --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function viewLargeImage(url) {
        Swal.fire({
            imageUrl: url,
            imageWidth: 'auto',
            imageHeight: 'auto',
            showCloseButton: true,
            showConfirmButton: false,
            background: '#fff',
            customClass: { popup: 'rounded-xl' }
        });
    }

    function viewVideo(url) {
        Swal.fire({
            html: `
                <video width="100%" height="450" controls autoplay style="border-radius: 12px; background: #000; outline: none;">
                    <source src="${url}" type="video/mp4">
                    Trình duyệt của bạn không hỗ trợ xem video trực tiếp.
                </video>
            `,
            width: '800px',
            showCloseButton: true,
            showConfirmButton: false,
            background: 'transparent'
        });
    }

    function toggleEdit(id) {
        $(`#replyDisplay${id}`).slideToggle(300);
        $(`#editReplyForm${id}`).collapse('toggle');
    }

    function confirmDeleteReply(id) {
        Swal.fire({
            title: 'Xóa câu trả lời?',
            text: "Trạng thái sẽ quay lại thành 'Chờ phản hồi'.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#4e73df',
            cancelButtonColor: '#858796',
            confirmButtonText: 'Đồng ý xóa',
            cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) document.getElementById('delete-reply-form-' + id).submit();
        });
    }

    function confirmDeleteReview(id) {
        Swal.fire({
            title: 'Xác nhận xóa đánh giá?',
            text: "Toàn bộ nội dung, ảnh và video của khách sẽ mất vĩnh viễn!",
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#e74a3b',
            cancelButtonColor: '#858796',
            confirmButtonText: 'Xóa ngay',
            cancelButtonText: 'Quay lại'
        }).then((result) => {
            if (result.isConfirmed) document.getElementById('delete-review-form-' + id).submit();
        });
    }

    $(document).ready(function() {
        const Toast = Swal.mixin({
            toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true
        });

        @if(session('success')) Toast.fire({ icon: 'success', title: "{{ session('success') }}" }); @endif
        @if(session('error')) Toast.fire({ icon: 'error', title: "{{ session('error') }}" }); @endif
    });
</script>
@endsection