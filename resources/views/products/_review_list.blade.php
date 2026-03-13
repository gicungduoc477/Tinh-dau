<style>
    /* 1. Review Filter Bar */
    .review-filter-bar {
        background: #f8fafc;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 25px;
        border: 1px solid #edf2f7;
    }

    /* 2. Filter Buttons Styling */
    .filter-btn {
        border: 1px solid #e2e8f0;
        background: white;
        color: #64748b;
        padding: 8px 18px;
        border-radius: 50px;
        font-size: 0.85rem;
        font-weight: 600;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .filter-btn:hover {
        border-color: #27ae60;
        color: #27ae60;
        background: #f0fff4;
    }

    .filter-btn.active {
        background: #27ae60 !important;
        color: white !important;
        border-color: #27ae60 !important;
        box-shadow: 0 4px 12px rgba(39, 174, 96, 0.25);
        transform: translateY(-2px);
    }

    /* 3. Review Item Layout */
    .review-item {
        border-bottom: 1px dashed #e2e8f0;
        padding-bottom: 25px;
        margin-bottom: 25px;
        transition: background 0.3s ease;
        border-radius: 12px;
    }

    .review-avatar {
        width: 48px;
        height: 48px;
        background: #f0fdf4;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        color: #198754;
        border: 2px solid #dcfce7;
        flex-shrink: 0;
    }

    /* 4. Shop Reply */
    .shop-reply-box {
        background-color: #f9fdfa !important;
        border-left: 4px solid #27ae60 !important;
        border-radius: 8px;
        padding: 15px;
        margin-top: 15px;
    }

    /* 5. Tag Styling */
    .review-tag-pill {
        font-size: 0.75rem;
        padding: 4px 12px;
        margin-right: 6px;
        margin-bottom: 8px;
        display: inline-block;
        border-radius: 50px;
        background: #f0fdf4;
        color: #16a34a;
        font-weight: 600;
        border: 1px solid #dcfce7;
    }

    /* 6. Media Styling (Image & Video) */
    .review-media-wrapper {
        position: relative;
        display: inline-block;
        margin-top: 10px;
    }

    .review-img-thumb, .review-video-thumb {
        transition: all 0.3s ease;
        border: 2px solid #fff;
        border-radius: 8px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.08);
        object-fit: cover;
        background: #000;
    }

    .review-img-thumb:hover {
        transform: scale(1.05);
    }

    .video-overlay-icon {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: white;
        font-size: 2rem;
        text-shadow: 0 2px 10px rgba(0,0,0,0.5);
        pointer-events: none;
    }

    /* Fade in animation */
    .ajax-fade-in {
        animation: fadeInReview 0.5s ease-in-out;
    }

    @keyframes fadeInReview {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<div id="review-list-wrapper" class="ajax-fade-in">
    {{-- Hiển thị tổng số đánh giá --}}
    @if(isset($reviews) && count($reviews) > 0)
        <div class="mb-3 ps-1">
            <small class="text-muted fw-bold text-uppercase" style="letter-spacing: 1px;">
                Tìm thấy {{ $reviews->total() }} đánh giá
            </small>
        </div>
    @endif

    @forelse($reviews as $review)
        <div class="review-item d-flex gap-3 p-2">
            {{-- Avatar --}}
            <div class="review-avatar">
                {{ strtoupper(substr($review->user->name ?? 'K', 0, 1)) }}
            </div>

            <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="mb-0 fw-bold text-dark">{{ $review->user->name ?? 'Khách hàng' }}</h6>
                        <div class="text-warning small my-1">
                            @for($i=1; $i<=5; $i++)
                                <i class="bi bi-star{{ $i <= $review->rating ? '-fill' : '' }}"></i>
                            @endfor
                        </div>
                    </div>
                    <span class="text-muted bg-light px-2 py-1 rounded" style="font-size: 0.7rem; font-weight: 600;">
                        <i class="bi bi-clock me-1"></i>{{ optional($review->created_at)->format('d/m/Y') }}
                    </span>
                </div>
                
                <div class="review-content-body mt-2">
                    {{-- 1. Hiển thị Tags --}}
                    @if($review->tags && count((array)$review->tags) > 0)
                        <div class="review-tags mb-1">
                            @foreach((array)$review->tags as $tag)
                                <span class="review-tag-pill">#{{ $tag }}</span>
                            @endforeach
                        </div>
                    @endif

                    {{-- 2. Hiển thị Comment --}}
                    @if($review->comment && trim($review->comment) != "")
                        <p class="mb-2 text-dark" style="line-height: 1.6; font-size: 0.95rem; white-space: pre-line;">
                            {{ $review->comment }}
                        </p>
                    @elseif(!($review->tags && count((array)$review->tags) > 0))
                        <p class="mb-2 text-muted small" style="font-style: italic;">
                            (Khách hàng không để lại bình luận văn bản)
                        </p>
                    @endif
                </div>

                {{-- 3. XỬ LÝ HIỂN THỊ MEDIA (ẢNH & VIDEO) --}}
                @if($review->image)
                    @php
                        $revMedia = trim($review->image);
                        // Tạo URL tuyệt đối
                        if (filter_var($revMedia, FILTER_VALIDATE_URL)) {
                            $mediaSrc = $revMedia;
                        } else {
                            $mediaSrc = str_contains($revMedia, 'storage/') ? asset($revMedia) : asset('storage/' . $revMedia);
                        }

                        // Kiểm tra đuôi file để xác định là video hay ảnh
                        $extension = strtolower(pathinfo($mediaSrc, PATHINFO_EXTENSION));
                        $isVideo = in_array($extension, ['mp4', 'mov', 'webm', 'ogg']);
                    @endphp

                    <div class="review-media-wrapper">
                        @if($isVideo)
                            {{-- Giao diện hiển thị Video --}}
                            <video class="review-video-thumb" style="width: 150px; height: 150px; cursor: pointer;" controls>
                                <source src="{{ $mediaSrc }}" type="video/{{ $extension == 'mov' ? 'mp4' : $extension }}">
                            </video>
                        @else
                            {{-- Giao diện hiển thị Ảnh --}}
                            <a href="{{ $mediaSrc }}" target="_blank" class="d-inline-block">
                                <img src="{{ $mediaSrc }}" 
                                     class="review-img-thumb" 
                                     style="width: 100px; height: 100px;"
                                     onerror="this.onerror=null; this.src='https://placehold.co/100x100?text=No+Image';">
                            </a>
                        @endif
                    </div>
                @endif

                {{-- Shop Reply --}}
                @if($review->reply)
                    <div class="shop-reply-box">
                        <div class="d-flex align-items-center mb-1">
                            <i class="bi bi-chat-right-quote-fill text-success me-2"></i>
                            <strong class="text-success small" style="text-transform: uppercase; font-size: 0.7rem;">Phản hồi từ NatureShop</strong>
                        </div>
                        <p class="mb-0 text-muted small" style="font-style: italic;">
                            {{ $review->reply }}
                        </p>
                    </div>
                @endif
            </div>
        </div>
    @empty
        <div class="text-center py-5">
            <div class="mb-3">
                <i class="bi bi-chat-square-dots text-muted opacity-25" style="font-size: 4rem;"></i>
            </div>
            <h5 class="text-muted fw-normal">Chưa có đánh giá nào cho mục này.</h5>
        </div>
    @endforelse

    {{-- Pagination --}}
    @if($reviews->hasPages())
        <div class="mt-5 d-flex justify-content-center ajax-pagination">
            {{ $reviews->links('pagination::bootstrap-4') }}
        </div>
    @endif
</div>