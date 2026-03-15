@extends('layouts.app')

@section('title', 'Đánh giá của tôi')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0"><i class="bi bi-star-half text-warning me-2"></i>Đánh giá sản phẩm</h3>
        <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill">
            <i class="bi bi-bag-check me-1"></i> Đơn hàng của tôi
        </a>
    </div>

    {{-- Hệ thống Tab phân loại --}}
    <ul class="nav nav-pills mb-4 shadow-sm p-2 bg-white rounded-pill" id="pills-tab" role="tablist">
        <li class="nav-item flex-fill">
            <button class="nav-link active rounded-pill w-100 fw-bold" id="pills-pending-tab" data-bs-toggle="pill" data-bs-target="#pending" type="button" role="tab">
                Chờ đánh giá ({{ $pendingReviews->count() }})
            </button>
        </li>
        <li class="nav-item flex-fill">
            <button class="nav-link rounded-pill w-100 fw-bold" id="pills-completed-tab" data-bs-toggle="pill" data-bs-target="#completed" type="button" role="tab">
                Đã đánh giá ({{ $completedReviews->count() }})
            </button>
        </li>
    </ul>

    <div class="tab-content mt-4" id="pills-tabContent">
        {{-- Tab 1: Chờ đánh giá --}}
        <div class="tab-pane fade show active" id="pending" role="tabpanel" aria-labelledby="pills-pending-tab">
            @forelse($pendingReviews as $item)
                @if($item->product) {{-- Đảm bảo sản phẩm tồn tại --}}
                <div class="card border-0 shadow-sm mb-3 rounded-4 p-3 border-start border-warning border-4">
                    <div class="row align-items-center">
                        <div class="col-md-2 col-4">
                            @php
                                $pPath = ltrim($item->product->image, '/');
                                if(str_starts_with($pPath, 'http')) {
                                    $pImg = $pPath;
                                } else {
                                    if (str_contains($pPath, 'uploads/product/')) {
                                        $pImg = asset($pPath);
                                    } else {
                                        $pImg = file_exists(public_path('uploads/product/' . $pPath))
                                                ? asset('uploads/product/' . $pPath)
                                                : asset('storage/' . $pPath);
                                    }
                                }
                            @endphp
                            <img src="{{ $pImg }}" class="img-fluid rounded-3 shadow-sm" alt="{{ $item->product->name }}" style="aspect-ratio: 1/1; object-fit: cover;" onerror="this.src='{{ asset('backend/img/no-image.png') }}';">
                        </div>
                        <div class="col-md-7 col-8">
                            <h6 class="fw-bold mb-1 text-dark">{{ $item->product->name }}</h6>
                            <p class="text-muted small mb-0">
                                <i class="bi bi-bag-check me-1"></i>Từ đơn hàng <a href="{{ route('orders.show', $item->order_id) }}" class="fw-bold text-decoration-none">#{{ $item->order->order_code }}</a>
                            </p>
                            <p class="text-success small mb-0"><i class="bi bi-calendar-check me-1"></i>Giao ngày {{ optional($item->order->updated_at)->format('d/m/Y') }}</p>
                        </div>
                        <div class="col-md-3 text-end mt-3 mt-md-0">
                            <a href="{{ route('reviews.create', ['product_id' => $item->product_id, 'order_id' => $item->order_id]) }}" class="btn btn-warning rounded-pill px-4 fw-bold shadow-sm">
                                <i class="bi bi-pencil-square me-1"></i> Đánh giá ngay
                            </a>
                        </div>
                    </div>
                </div>
                @endif
            @empty
                <div class="text-center py-5 bg-white rounded-4 shadow-sm">
                    <img src="https://cdn-icons-png.flaticon.com/512/4076/4076432.png" width="100" class="mb-3 opacity-50">
                    <p class="text-muted mb-0">Tuyệt vời! Bạn đã hoàn thành tất cả đánh giá.</p>
                </div>
            @endforelse
        </div>

        {{-- Tab 2: Đã đánh giá --}}
        <div class="tab-pane fade" id="completed" role="tabpanel" aria-labelledby="pills-completed-tab">
            @forelse($completedReviews as $review)
                <div class="card border-0 shadow-sm mb-3 rounded-4 p-3 bg-white">
                    <div class="row">
                        {{-- Cột Ảnh sản phẩm --}}
                        <div class="col-md-2 col-3">
                            @php
                                $productImgName = trim($review->product->image ?? '');
                                if (filter_var($productImgName, FILTER_VALIDATE_URL)) {
                                    $productImgShow = $productImgName;
                                } elseif (!empty($productImgName)) {
                                    $pNameClean = ltrim($productImgName, '/');
                                    if (str_contains($pNameClean, 'uploads/product/')) {
                                        $productImgShow = asset($pNameClean);
                                    } else {
                                        $productImgShow = file_exists(public_path('uploads/product/' . $pNameClean)) 
                                                            ? asset('uploads/product/' . $pNameClean) 
                                                            : asset('storage/' . $pNameClean);
                                    }
                                } else {
                                    $productImgShow = asset('backend/img/no-image.png');
                                }
                            @endphp
                            <img src="{{ $productImgShow }}" 
                                 class="img-fluid rounded-3 shadow-sm" 
                                 alt="{{ $review->product->name ?? 'Sản phẩm' }}"
                                 style="aspect-ratio: 1/1; object-fit: cover;"
                                 onerror="this.onerror=null; this.src='{{ asset('backend/img/no-image.png') }}';">
                        </div>
                        
                        <div class="col-md-10 col-9">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="fw-bold text-dark mb-1">{{ $review->product->name ?? 'Sản phẩm' }}</h6>
                                    <div class="text-warning mb-2">
                                        @for($i=1; $i<=5; $i++)
                                            <i class="bi bi-star{{ $i <= $review->rating ? '-fill' : '' }}"></i>
                                        @endfor
                                    </div>
                                </div>
                                <span class="badge bg-light text-muted fw-normal rounded-pill">
                                    {{ $review->created_at->format('d/m/Y') }}
                                </span>
                            </div>

                            <div class="bg-light p-3 rounded-3 position-relative mb-2">
                                <i class="bi bi-quote text-secondary opacity-25 position-absolute top-0 start-0 ms-1 mt-1" style="font-size: 1.5rem;"></i>
                                <p class="small italic text-dark mb-0 ps-3">"{{ $review->comment }}"</p>
                            </div>

                            {{-- Media đính kèm (Ảnh hoặc Video) --}}
                            @if($review->image)
                                <div class="mt-2">
                                    @php
                                        $rMedia = ltrim($review->image, '/');
                                        if(str_starts_with($rMedia, 'http')) {
                                            $finalMediaUrl = $rMedia;
                                        } else {
                                            $finalMediaUrl = file_exists(public_path('storage/' . $rMedia)) 
                                                             ? asset('storage/' . $rMedia) 
                                                             : asset('uploads/reviews/' . $rMedia);
                                        }
                                        $extension = strtolower(pathinfo($finalMediaUrl, PATHINFO_EXTENSION));
                                        $isVideo = in_array($extension, ['mp4', 'mov', 'webm', 'ogg']);
                                    @endphp

                                    @if($isVideo)
                                        <video width="120" height="120" class="rounded-3 shadow-sm border" controls style="object-fit: cover; background: #000;">
                                            <source src="{{ $finalMediaUrl }}" type="video/{{ $extension == 'mov' ? 'mp4' : $extension }}">
                                        </video>
                                    @else
                                        <a href="{{ $finalMediaUrl }}" target="_blank">
                                            <img src="{{ $finalMediaUrl }}" 
                                                 class="img-thumbnail rounded-3" 
                                                 style="width: 80px; height: 80px; object-fit: cover; cursor: zoom-in;"
                                                 onerror="this.style.display='none';">
                                        </a>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-5 bg-white rounded-4 shadow-sm">
                    <p class="text-muted mb-0">Bạn chưa thực hiện đánh giá nào.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<style>
    .nav-pills .nav-link { color: #6c757d; transition: 0.3s; border: 1px solid transparent; }
    .nav-pills .nav-link.active { background-color: #198754 !important; color: white !important; }
    .card { transition: all 0.3s ease; }
    .card:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important; }
    .italic { font-style: italic; }
    .img-thumbnail:hover { border-color: #198754; }
    video:focus { outline: none; }
</style>
@endsection