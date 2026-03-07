@forelse($reviews as $review)
    <div class="review-item d-flex gap-3 animate__animated animate__fadeIn">
        <div class="review-avatar flex-shrink-0">
            {{ strtoupper(substr($review->user->name, 0, 1)) }}
        </div>
        <div class="flex-grow-1">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h6 class="mb-0 fw-bold">{{ $review->user->name }}</h6>
                    <div class="text-warning small my-1">
                        @for($i=1; $i<=5; $i++)
                            <i class="bi bi-star{{ $i <= $review->rating ? '-fill' : '' }}"></i>
                        @endfor
                    </div>
                </div>
                <small class="text-muted">{{ $review->created_at->format('d/m/Y') }}</small>
            </div>
            <p class="mb-2 text-dark mt-2" style="line-height: 1.6;">{{ $review->comment }}</p>

            @if($review->image)
                <div class="review-images mt-3">
                    <img src="{{ asset('storage/' . $review->image) }}" class="review-img-thumb border shadow-sm">
                </div>
            @endif

            @if($review->reply)
                <div class="shop-reply-box">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-reply-fill text-success me-2" style="transform: scaleX(-1);"></i>
                        <strong class="text-success small text-uppercase">Phản hồi từ NATURESHOP</strong>
                    </div>
                    <p class="mb-0 text-secondary small italic">{{ $review->reply }}</p>
                </div>
            @endif
        </div>
    </div>
@empty
    <div class="text-center py-5">
        <i class="bi bi-chat-left-dots fs-1 text-muted opacity-25"></i>
        <p class="text-muted mt-3">Không có đánh giá nào phù hợp với bộ lọc.</p>
    </div>
@endforelse

<div class="mt-4 d-flex justify-content-center ajax-pagination">
    {{ $reviews->links('pagination::bootstrap-4') }}
</div>