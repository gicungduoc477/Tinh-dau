@extends('layouts.app')

@section('title', 'Chi tiết đơn hàng #' . $order->id)

@section('content')
<style>
    .sticky-timeline { position: -webkit-sticky; position: sticky; top: 20px; max-height: calc(100vh - 40px); overflow-y: auto; }
    .timeline { border-left: 2px solid #f8f9fa; margin-left: 10px; }
    .timeline-item { position: relative; }
    .timeline-dot { width: 14px; height: 14px; border-radius: 50%; position: absolute; left: -8px; top: 4px; border: 3px solid #fff; box-shadow: 0 0 5px rgba(0,0,0,0.1); }
    .img-evidence { max-width: 150px; transition: transform 0.2s; cursor: pointer; }
    .img-evidence:hover { transform: scale(1.05); }
</style>

<div class="container mt-5 mb-5">
    <div class="row g-4"> 
        <div class="col-lg-8">
            <div class="card p-4 border-0 shadow-sm rounded-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold mb-0 text-dark">Đơn hàng #{{ $order->order_code }}</h4>
                    <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill"> Quay lại</a>
                </div>

                <div class="d-flex align-items-center mb-4 bg-light p-3 rounded-3">
                    <span class="badge bg-{{ $order->status_color }} px-3 py-2 rounded-pill shadow-sm">
                        {{ $order->status_label }}
                    </span>
                    @if(method_exists($order, 'canBeReturned') && $order->canBeReturned())
                        <button type="button" class="btn btn-link text-danger btn-sm fw-bold text-decoration-none ms-3" data-bs-toggle="modal" data-bs-target="#returnModal">
                            Khiếu nại / Trả hàng
                        </button>
                    @endif
                </div>

                {{-- THÔNG TIN KHIẾU NẠI & ẢNH MINH CHỨNG --}}
                @if(in_array($order->status, ['returning', 'returned', 'refunding', 'refunded']))
                    <div class="alert alert-secondary border-0 rounded-3 mb-4">
                        <div class="row mt-2">
                            <div class="col-md-6 border-end">
                                <p class="mb-1 small text-muted">Lý do khiếu nại: <strong>{{ $order->return_reason }}</strong></p>
                                
                                @if($order->return_image)
                                    @php
                                        $rPath = ltrim($order->return_image, '/');
                                        // 1. Kiểm tra trong storage/
                                        $storageFull = 'storage/' . (str_contains($rPath, 'returns/') ? $rPath : 'returns/' . $rPath);
                                        // 2. Kiểm tra trong uploads/ (ảnh cũ của bạn)
                                        $uploadsFull = 'uploads/product/' . (str_contains($rPath, 'returns/') ? $rPath : 'returns/' . $rPath);
                                        
                                        if (file_exists(public_path($storageFull))) {
                                            $finalUrl = asset($storageFull);
                                        } elseif (file_exists(public_path($uploadsFull))) {
                                            $finalUrl = asset($uploadsFull);
                                        } else {
                                            $finalUrl = asset($storageFull); // Fallback
                                        }
                                    @endphp
                                    <div class="mt-2">
                                        <p class="mb-1 small text-muted">Ảnh minh chứng:</p>
                                        <img src="{{ $finalUrl }}" class="rounded shadow-sm border img-evidence" 
                                             onclick="window.open(this.src)"
                                             onerror="this.src='{{ asset('backend/img/no-image.png') }}';">
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1 small text-muted">Hoàn tiền qua: <strong>{{ $order->bank_name }}</strong></p>
                                <p class="mb-0 small">STK: {{ $order->account_number }}</p>
                                <p class="mb-0 small">Chủ thẻ: {{ strtoupper($order->account_holder) }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- DANH SÁCH SẢN PHẨM --}}
                <h5 class="fw-bold mb-3 small text-uppercase text-muted">Sản phẩm</h5>
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
                                                    $pPath = ltrim($it->product->image, '/');
                                                    if(str_starts_with($pPath, 'http')) {
                                                        $pImg = $pPath;
                                                    } else {
                                                        // Tương tự: Check uploads/product trước vì ảnh sản phẩm thường nằm ở đây
                                                        $checkPath = str_contains($pPath, 'uploads/product/') ? $pPath : 'uploads/product/' . $pPath;
                                                        $pImg = file_exists(public_path($checkPath)) ? asset($checkPath) : asset('storage/' . $pPath);
                                                    }
                                                }
                                            @endphp
                                            <img src="{{ $pImg }}" class="rounded me-3" style="width: 65px; height: 65px; object-fit: cover;" onerror="this.src='{{ asset('backend/img/no-image.png') }}';">
                                            <div>
                                                <div class="fw-bold small">{{ $it->product->name ?? 'Sản phẩm' }}</div>
                                                <div class="text-muted small">{{ number_format($it->price, 0, ',', '.') }} đ x {{ $it->quantity }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end border-0 fw-bold">{{ number_format($it->price * $it->quantity, 0, ',', '.') }} đ</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3 p-3 bg-light rounded-3 d-flex justify-content-between border">
                    <span class="fw-bold">TỔNG THANH TOÁN:</span>
                    <h4 class="fw-bold text-danger mb-0">{{ number_format($order->total_price, 0, ',', '.') }} đ</h4>
                </div>
            </div>
        </div>

        {{-- TIMELINE --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 sticky-timeline">
                <h6 class="fw-bold mb-4">Lịch sử đơn hàng</h6>
                <div class="timeline">
                    @foreach($order->statusHistories as $history)
                        <div class="timeline-item pb-4">
                            <div class="timeline-dot bg-{{ $history->to_status_color }}"></div>
                            <div class="ms-4">
                                <div class="fw-bold small">{{ $history->to_status_label }}</div>
                                <div class="text-muted" style="font-size: 0.7rem;">{{ $history->created_at->format('H:i d/m/Y') }}</div>
                                @if($history->note)<div class="small text-secondary italic">"{{ $history->note }}"</div>@endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL --}}
<div class="modal fade" id="returnModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <form action="{{ route('orders.requestReturn', $order->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header border-0"><h5 class="modal-title fw-bold">Khiếu nại / Trả hàng</h5></div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Lý do <span class="text-danger">*</span></label>
                        <select name="return_reason" class="form-select" required>
                            <option value="Sản phẩm lỗi/hư hỏng">Sản phẩm lỗi/hư hỏng</option>
                            <option value="Giao sai sản phẩm">Giao sai sản phẩm</option>
                            <option value="Khác">Lý do khác</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Ảnh minh chứng <span class="text-danger">*</span></label>
                        <input type="file" name="return_image" class="form-control" accept="image/*" required>
                    </div>
                    <div class="bg-light p-3 rounded-3 mb-3">
                        <input type="text" name="bank_name" class="form-control mb-2" placeholder="Ngân hàng" required>
                        <input type="text" name="account_number" class="form-control mb-2" placeholder="Số tài khoản" required>
                        <input type="text" name="account_holder" class="form-control" placeholder="Tên chủ thẻ (VIET HOA)" required>
                    </div>
                    <textarea name="return_note" class="form-control" rows="2" placeholder="Ghi chú thêm..."></textarea>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" class="btn btn-danger w-100 rounded-pill fw-bold py-2">GỬI YÊU CẦU</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection