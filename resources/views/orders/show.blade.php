@extends('layouts.app')

@section('title', 'Chi tiết đơn hàng #' . $order->id)

@section('content')
<div class="container mt-5 mb-5">
    <div class="row">
        {{-- Cột bên trái: Thông tin đơn hàng --}}
        <div class="col-lg-8">
            <div class="card p-4 border-0 shadow-sm rounded-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold mb-0 text-dark">Đơn hàng #{{ $order->order_code }}</h4>
                    <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill">
                        <i class="bi bi-arrow-left"></i> Quay lại
                    </a>
                </div>

                @php
                    $canReturn = method_exists($order, 'canBeReturned') ? $order->canBeReturned() : false;
                    $isExpiredReview = false;
                    if($order->status === 'success' && $order->updated_at) {
                        $isExpiredReview = $order->updated_at->diffInDays(now()) > 30;
                    }
                @endphp
                
                <div class="d-flex align-items-center mb-4 bg-light p-3 rounded-3">
                    <span class="text-muted me-2">Trạng thái:</span>
                    <span class="badge bg-{{ $order->status_color }} px-3 py-2 rounded-pill shadow-sm">
                        {{ $order->status_label }}
                    </span>
                    
                    @if($canReturn)
                        <div class="ms-3 d-flex align-items-center">
                            <button type="button" class="btn btn-link text-danger btn-sm p-0 fw-bold text-decoration-none me-2" data-bs-toggle="modal" data-bs-target="#returnModal">
                                <i class="bi bi-exclamation-octagon me-1"></i> Khiếu nại / Trả hàng
                            </button>
                            <small class="text-muted italic">(Còn {{ $order->return_time_left }})</small>
                        </div>
                    @endif
                </div>

                <hr class="opacity-10">

                {{-- Thông tin khiếu nại & Ngân hàng hoàn tiền --}}
                @if($order->status === 'returning' || $order->status === 'returned' || $order->status === 'refunding' || $order->status === 'refunded')
                    <div class="alert alert-secondary border-0 rounded-3 mb-4">
                        <h6 class="fw-bold text-dark"><i class="bi bi-info-circle me-2"></i>Thông tin hoàn hàng & Hoàn tiền</h6>
                        <div class="row mt-3">
                            <div class="col-md-6 border-end">
                                <p class="mb-1 small text-muted">Lý do khiếu nại:</p>
                                <p class="fw-bold">{{ $order->return_reason }}</p>
                                @if($order->return_image)
                                    <div class="mt-2">
                                        <img src="{{ Storage::url($order->return_image) }}" class="rounded shadow-sm" style="max-width: 100px; cursor: pointer;" data-bs-toggle="modal" data-bs-target="#imagePreviewModal">
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1 small text-muted">Tài khoản nhận tiền hoàn:</p>
                                <p class="mb-0"><strong>Ngân hàng:</strong> {{ $order->bank_name }}</p>
                                <p class="mb-0"><strong>STK:</strong> {{ $order->account_number }}</p>
                                <p class="mb-0"><strong>Chủ thẻ:</strong> {{ strtoupper($order->account_holder) }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="fw-bold text-success mb-3"><i class="bi bi-truck me-2"></i>Giao hàng</h6>
                        <p class="mb-1"><strong>{{ $order->name }}</strong></p>
                        <p class="mb-1 small">{{ $order->phone_number }}</p>
                        <p class="mb-1 small text-muted">{{ $order->shipping_address }}</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold text-success mb-3"><i class="bi bi-credit-card me-2"></i>Thanh toán</h6>
                        <p class="mb-1 small">Phương thức: <span class="text-uppercase">{{ $order->payment_method ?? 'cod' }}</span></p>
                        <p class="mb-1">
                            <span class="badge bg-{{ $order->payment_status === 'paid' ? 'success' : 'light text-danger border' }}">
                                {{ $order->payment_status === 'paid' ? 'Đã thanh toán' : ($order->payment_status === 'refunded' ? 'Đã hoàn tiền' : 'Chưa thanh toán') }}
                            </span>
                        </p>
                    </div>
                </div>

                <h5 class="fw-bold mb-3 small text-uppercase text-muted">Sản phẩm</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <tbody>
                            @foreach($order->items as $it)
                                <tr>
                                    <td class="ps-0 border-0">
                                        <div class="d-flex align-items-center">
                                            @if($it->product && $it->product->image)
                                                <img src="{{ $it->product->image_url }}" class="rounded me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                            @else
                                                <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;"><i class="bi bi-image text-muted"></i></div>
                                            @endif
                                            <div>
                                                <div class="fw-bold small">{{ $it->product->name ?? 'Sản phẩm không tồn tại' }}</div>
                                                <div class="text-muted small">x{{ $it->quantity }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end border-0 fw-bold small">{{ number_format($it->price * $it->quantity, 0, ',', '.') }} đ</td>
                                    <td class="text-end border-0 pe-0">
                                        @if($order->status === 'success' && $it->product)
                                            @if(!$isExpiredReview)
                                                @php $hasReviewed = \App\Models\Review::where('user_id', auth()->id())->where('product_id', $it->product_id)->exists(); @endphp
                                                @if(!$hasReviewed)
                                                    <a href="{{ route('reviews.create', $it->product_id) }}" class="btn btn-outline-success btn-sm rounded-pill">Đánh giá</a>
                                                @else
                                                    <i class="bi bi-check-circle text-success" title="Đã đánh giá"></i>
                                                @endif
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 p-3 bg-light rounded-3 d-flex justify-content-between align-items-center">
                    <span class="text-muted">Tổng cộng:</span>
                    <h4 class="fw-bold text-danger mb-0">{{ number_format($order->total_price, 0, ',', '.') }} đ</h4>
                </div>
            </div>
        </div>

        {{-- Cột bên phải: Timeline --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 sticky-top" style="top: 20px; z-index: 1000;">
                <h6 class="fw-bold mb-4 text-dark"><i class="bi bi-clock-history me-2"></i>Lịch sử đơn hàng</h6>
                <div class="timeline">
                    @forelse($order->statusHistories as $history)
                        <div class="timeline-item pb-4 position-relative">
                            <div class="timeline-dot bg-{{ $history->to_status_color }}"></div>
                            <div class="timeline-content ms-4">
                                <div class="fw-bold small text-dark">{{ $history->to_status_label }}</div>
                                <div class="text-muted small" style="font-size: 0.7rem;">{{ $history->created_at->format('H:i - d/m/Y') }}</div>
                                @if($history->note)
                                    <div class="bg-light p-2 rounded mt-2 small text-secondary italic border-start border-3 border-{{ $history->to_status_color }}">
                                        "{{ $history->note }}"
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="timeline-item pb-4 position-relative">
                            <div class="timeline-dot bg-warning"></div>
                            <div class="timeline-content ms-4 small text-muted">Đang cập nhật dữ liệu...</div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL KHIẾU NẠI TRẢ HÀNG + NHẬP THÔNG TIN NGÂN HÀNG --}}
<div class="modal fade" id="returnModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <form action="{{ route('orders.requestReturn', $order->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Yêu cầu trả hàng & Hoàn tiền</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning py-2 small mb-3">
                        <i class="bi bi-info-circle-fill me-2"></i> Vui lòng cung cấp thông tin chính xác để chúng tôi hoàn tiền qua ngân hàng.
                    </div>
                    
                    {{-- Phần lý do --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Lý do khiếu nại <span class="text-danger">*</span></label>
                        <select name="return_reason" class="form-select" required>
                            <option value="Sản phẩm lỗi/hư hỏng">Sản phẩm lỗi/hư hỏng</option>
                            <option value="Giao sai sản phẩm">Giao sai sản phẩm</option>
                            <option value="Sản phẩm khác mô tả">Sản phẩm khác mô tả</option>
                            <option value="Khác">Lý do khác</option>
                        </select>
                    </div>

                    {{-- Phần ảnh --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Ảnh minh chứng <span class="text-danger">*</span></label>
                        <input type="file" name="return_image" class="form-control" accept="image/*" required>
                    </div>

                    {{-- Phần Ngân hàng (MỚI) --}}
                    <div class="bg-light p-3 rounded-3 mb-3 border">
                        <h6 class="fw-bold small mb-3 text-primary"><i class="bi bi-bank me-2"></i>Tài khoản nhận lại tiền</h6>
                        <div class="mb-2">
                            <label class="form-label small mb-1">Tên ngân hàng (Ví dụ: VCB, MB, ACB...)</label>
                            <input type="text" name="bank_name" class="form-control form-control-sm" placeholder="Nhập tên viết tắt ngân hàng" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small mb-1">Số tài khoản</label>
                            <input type="text" name="account_number" class="form-control form-control-sm" placeholder="Nhập STK chính xác" required>
                        </div>
                        <div class="mb-0">
                            <label class="form-label small mb-1">Họ tên chủ thẻ</label>
                            <input type="text" name="account_holder" class="form-control form-control-sm" placeholder="VIET HOA KHONG DAU" required>
                        </div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-bold small">Mô tả chi tiết</label>
                        <textarea name="return_note" class="form-control" rows="2" placeholder="Cung cấp thêm thông tin nếu có..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 shadow-sm">Gửi yêu cầu</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL XEM ẢNH --}}
<div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 bg-transparent">
            <div class="modal-body p-0 text-center">
                <img src="{{ Storage::url($order->return_image) }}" class="img-fluid rounded shadow">
            </div>
        </div>
    </div>
</div>

<style>
    .timeline { border-left: 2px solid #eee; margin-left: 10px; }
    .timeline-dot {
        width: 12px; height: 12px; border-radius: 50%;
        position: absolute; left: -7px; top: 4px;
        border: 2px solid #fff; box-shadow: 0 0 4px rgba(0,0,0,0.1);
    }
    .italic { font-style: italic; }
    .sticky-top { z-index: 1020; }
</style>
@endsection