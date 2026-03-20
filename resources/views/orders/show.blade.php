@extends('layouts.app')

@section('title', 'Chi tiết đơn hàng #' . $order->order_code)

@section('content')
<style>
    .sticky-timeline { position: -webkit-sticky; position: sticky; top: 20px; max-height: calc(100vh - 40px); overflow-y: auto; }
    .timeline { border-left: 2px solid #e9ecef; margin-left: 10px; }
    .timeline-item { position: relative; }
    .timeline-dot { width: 14px; height: 14px; border-radius: 50%; position: absolute; left: -8px; top: 4px; border: 3px solid #fff; box-shadow: 0 0 5px rgba(0,0,0,0.1); }
    .img-evidence { max-width: 150px; transition: transform 0.2s; cursor: pointer; border: 1px solid #dee2e6; }
    .img-evidence:hover { transform: scale(1.05); z-index: 10; }
    
    .btn-review { 
        background-color: #ff9800; 
        color: white; 
        border: none;
        transition: 0.3s;
    }
    .btn-review:hover { 
        background-color: #e68a00; 
        color: white; 
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(255, 152, 0, 0.3);
    }
</style>

<div class="container mt-4 mt-lg-5 mb-5">
    <div class="row g-4"> 
        {{-- CỘT TRÁI: CHI TIẾT ĐƠN HÀNG --}}
        <div class="col-lg-8">
            <div class="card p-3 p-lg-4 border-0 shadow-sm rounded-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold mb-0 text-dark">Đơn hàng #{{ $order->order_code }}</h4>
                    <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3"> 
                        <i class="fas fa-arrow-left me-1"></i> Quay lại
                    </a>
                </div>

                <div class="d-flex align-items-center mb-4 bg-light p-3 rounded-4 flex-wrap gap-2">
                    <span class="badge bg-{{ $order->status_color }} px-3 py-2 rounded-pill shadow-sm">
                        {{ $order->status_label }}
                    </span>

                    {{-- NÚT KHIẾU NẠI: Tự động ẩn nếu đã gửi hoặc hết hạn --}}
                    @if($order->canBeReturned())
                        <button type="button" class="btn btn-link text-danger btn-sm fw-bold text-decoration-none ms-lg-auto" data-bs-toggle="modal" data-bs-target="#returnModal">
                            <i class="fas fa-exclamation-triangle me-1"></i> Khiếu nại / Trả hàng
                        </button>
                    @endif
                </div>

                {{-- HIỂN THỊ THÔNG TIN KHIẾU NẠI (NẾU ĐANG TRONG QUÁ TRÌNH KHIẾU NẠI) --}}
                @if(in_array($order->status, ['returning', 'returned', 'refunding', 'refunded']))
                    <div class="alert alert-secondary border-0 rounded-4 mb-4 shadow-sm">
                        <div class="row g-3">
                            <div class="col-md-7 border-end-md">
                                <h6 class="fw-bold text-dark mb-2"><i class="fas fa-info-circle me-2"></i>Thông tin khiếu nại</h6>
                                <p class="mb-2 small">Lý do: <strong class="text-danger">{{ $order->return_reason }}</strong></p>
                                
                                {{-- Xử lý hiển thị mảng ảnh an toàn --}}
                                @if($order->hasReturnImages())
                                    <div class="mt-2">
                                        <p class="mb-2 small text-muted fw-bold">Ảnh minh chứng:</p>
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach($order->return_image as $imageUrl)
                                                <img src="{{ $imageUrl }}" class="rounded-3 img-evidence" 
                                                     style="width: 80px; height: 80px; object-fit: cover;"
                                                     onclick="window.open(this.src)"
                                                     onerror="this.src='{{ asset('backend/img/no-image.png') }}';">
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                @if($order->return_note)
                                    <div class="mt-3 p-2 bg-white rounded-3 border-start border-3 border-warning small fst-italic">
                                        "{{ $order->return_note }}"
                                    </div>
                                @endif
                            </div>

                            <div class="col-md-5 ps-md-4">
                                <h6 class="fw-bold text-dark mb-2"><i class="fas fa-university me-2"></i>Nhận hoàn tiền</h6>
                                <div class="bg-white p-2 rounded-3 border shadow-xs">
                                    <p class="mb-1 small">Ngân hàng: <strong>{{ $order->bank_name }}</strong></p>
                                    <p class="mb-1 small">Số tài khoản: <strong class="text-primary">{{ $order->account_number }}</strong></p>
                                    <p class="mb-0 small text-uppercase">Chủ thẻ: <strong>{{ $order->account_holder }}</strong></p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- DANH SÁCH SẢN PHẨM --}}
                <h5 class="fw-bold mb-3 small text-uppercase text-muted">Sản phẩm đã mua</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <tbody>
                            @foreach($order->items as $it)
                                <tr>
                                    <td class="ps-0 border-0">
                                        <div class="d-flex align-items-center">
                                            @php
                                                $pImg = asset('backend/img/no-image.png');
                                                if($it->product && $it->product->image) {
                                                    $pPath = $it->product->image;
                                                    $pImg = str_starts_with($pPath, 'http') ? $pPath : asset('storage/' . ltrim($pPath, '/'));
                                                }
                                            @endphp
                                            <img src="{{ $pImg }}" class="rounded-3 me-3 shadow-xs" style="width: 65px; height: 65px; object-fit: cover;" onerror="this.src='{{ asset('backend/img/no-image.png') }}';">
                                            <div>
                                                <div class="fw-bold text-dark small mb-1">{{ $it->product->name ?? 'Sản phẩm đã xóa' }}</div>
                                                <div class="text-muted small">{{ number_format($it->price, 0, ',', '.') }} đ x {{ $it->quantity }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end border-0 fw-bold text-dark">{{ number_format($it->price * $it->quantity, 0, ',', '.') }} đ</td>
                                    <td class="text-end border-0">
                                        @if($order->status == 'success' && $it->product)
                                            <a href="{{ route('reviews.create', ['product_id' => $it->product->id, 'order_id' => $order->id]) }}" class="btn btn-review btn-sm rounded-pill px-3">
                                                <i class="fas fa-star me-1"></i> Đánh giá
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-3 p-3 bg-light rounded-4 d-flex justify-content-between align-items-center border">
                    <span class="fw-bold text-muted small text-uppercase">Tổng thanh toán:</span>
                    <h4 class="fw-bold text-danger mb-0">{{ number_format($order->total_price, 0, ',', '.') }} đ</h4>
                </div>
            </div>
        </div>

        {{-- CỘT PHẢI: TIMELINE --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 sticky-timeline">
                <h6 class="fw-bold mb-4"><i class="fas fa-history me-2 text-primary"></i>Lịch sử đơn hàng</h6>
                <div class="timeline">
                    @foreach($order->statusHistories as $history)
                        <div class="timeline-item pb-4">
                            {{-- Sử dụng logic màu từ History nếu có, không thì dùng mặc định --}}
                            <div class="timeline-dot bg-{{ $history->to_status_color ?? 'primary' }}"></div>
                            <div class="ms-4">
                                <div class="fw-bold small text-dark">{{ $history->to_status_label ?? $history->to_status }}</div>
                                <div class="text-muted small" style="font-size: 0.7rem;">{{ $history->created_at->format('H:i - d/m/Y') }}</div>
                                @if($history->note)
                                    <div class="small text-secondary fst-italic mt-1 p-2 bg-light rounded" style="font-size: 0.8rem;">
                                        "{{ $history->note }}"
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL KHIẾU NẠI --}}
<div class="modal fade" id="returnModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form action="{{ route('orders.requestReturn', $order->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold text-danger"><i class="fas fa-undo me-2"></i>Yêu cầu Trả hàng / Hoàn tiền</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Lý do khiếu nại <span class="text-danger">*</span></label>
                        <select name="return_reason" class="form-select rounded-3 shadow-none" required>
                            <option value="">-- Chọn lý do --</option>
                            <option value="Sản phẩm lỗi/hư hỏng">Sản phẩm lỗi/hư hỏng</option>
                            <option value="Giao sai sản phẩm">Giao sai sản phẩm</option>
                            <option value="Sản phẩm hết hạn">Sản phẩm hết hạn / Không tươi</option>
                            <option value="Khác">Lý do khác</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Ảnh minh chứng <span class="text-danger">*</span></label>
                        <input type="file" name="return_images[]" class="form-control rounded-3 shadow-none" accept="image/*" required multiple>
                        <div class="form-text text-muted" style="font-size: 0.75rem;">Bạn có thể chọn tối đa 5 ảnh minh chứng.</div>
                    </div>
                    <div class="bg-light p-3 rounded-4 mb-3 border">
                        <p class="small fw-bold mb-2"><i class="fas fa-university me-2"></i>Thông tin nhận hoàn tiền</p>
                        <input type="text" name="bank_name" class="form-control mb-2 rounded-3 border-0 shadow-sm" placeholder="Tên ngân hàng (ví dụ: Vietcombank)" required>
                        <input type="text" name="account_number" class="form-control mb-2 rounded-3 border-0 shadow-sm" placeholder="Số tài khoản" required>
                        <input type="text" name="account_holder" class="form-control rounded-3 border-0 shadow-sm" placeholder="Tên chủ thẻ (VIẾT HOA KHÔNG DẤU)" required>
                    </div>
                    <textarea name="return_note" class="form-control rounded-4 shadow-none" rows="3" placeholder="Mô tả chi tiết vấn đề bạn gặp phải..."></textarea>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger rounded-pill fw-bold px-4 py-2 shadow">GỬI YÊU CẦU</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection