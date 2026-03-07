@extends('admin.layout.admin_layout')
@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Danh sách chờ hoàn tiền</h1>
        <div class="alert alert-warning mb-0 py-2 shadow-sm border-left-warning">
            <i class="fas fa-wallet mr-2"></i> Tổng tiền cần hoàn: 
            <strong class="text-danger">{{ number_format($orders->sum('total_price'), 0, ',', '.') }}đ</strong>
        </div>
    </div>
    
    <p class="mb-4 text-muted">Mẹo: Sử dụng App Ngân hàng quét mã QR để tự động điền <strong>số tiền</strong> và <strong>nội dung</strong>.</p>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th width="10%">Mã đơn</th>
                            <th>Khách hàng</th>
                            <th>Số tiền hoàn</th>
                            <th>Thông tin ngân hàng</th>
                            <th width="180px">Mã QR Thanh Toán</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                        <tr>
                            <td class="align-middle fw-bold text-dark">#{{ $order->order_code }}</td>
                            <td class="align-middle">
                                <span class="font-weight-bold">{{ $order->customer_name }}</span><br>
                                <small class="text-muted"><i class="fas fa-phone fa-sm"></i> {{ $order->phone_number }}</small>
                                <br>
                                <span class="badge badge-{{ $order->status == 'returning' ? 'warning' : 'primary' }}">
                                    {{ $order->status == 'returning' ? 'Khách vừa khiếu nại' : 'Đang xử lý hoàn' }}
                                </span>
                            </td>
                            <td class="align-middle text-center">
                                <span class="text-danger font-weight-bold" style="font-size: 1.15rem;">
                                    {{ number_format($order->total_price, 0, ',', '.') }}đ
                                </span>
                            </td>
                            <td class="align-middle bg-light">
                                @if($order->account_number)
                                    <div class="p-1">
                                        <span class="badge badge-info mb-1">{{ strtoupper($order->bank_name) }}</span><br>
                                        <span class="text-primary font-weight-bold" id="stk_{{ $order->id }}">{{ $order->account_number }}</span>
                                        <button class="btn btn-sm btn-link p-0 ml-1" onclick="copyToClipboard('{{ $order->account_number }}')" title="Copy STK">
                                            <i class="far fa-copy text-secondary"></i>
                                        </button><br>
                                        <small class="text-uppercase text-dark font-weight-bold">{{ $order->account_holder }}</small>
                                    </div>
                                @else
                                    <div class="p-2 text-center text-warning small italic">
                                        <i class="fas fa-exclamation-triangle"></i><br>
                                        Chưa có thông tin chuyển khoản
                                    </div>
                                @endif
                            </td>
                            <td class="text-center align-middle">
                                @if($order->account_number)
                                    @php
                                        // 1. Chuẩn hóa mã ngân hàng (Mapping BIN VietQR)
                                        $bankList = [
                                            'momo' => '970422', 'mbbank' => '970422', 'mb' => '970422',
                                            'vietcombank' => '970436', 'vcb' => '970436',
                                            'vietinbank' => '970415', 'acb' => '970416',
                                            'bidv' => '970418', 'agribank' => '970405',
                                            'tpbank' => '970423', 'techcombank' => '970407', 'tcb' => '970407'
                                        ];
                                        
                                        $inputBank = strtolower(trim($order->bank_name));
                                        $bankId = $bankList[$inputBank] ?? '970422'; // Mặc định về MB nếu không khớp
                                        
                                        $amount = intval($order->total_price);
                                        $description = 'Hoan tien don ' . $order->order_code;
                                        
                                        // Sử dụng template 'compact' để QR gọn gàng hơn trong table
                                        $qrUrl = "https://img.vietqr.io/image/{$bankId}-{$order->account_number}-compact.png?amount={$amount}&addInfo=" . urlencode($description) . "&accountName=" . urlencode($order->account_holder);
                                    @endphp
                                    
                                    <div class="qr-wrapper">
                                        <a href="{{ $qrUrl }}" target="_blank" title="Click để xem ảnh lớn">
                                            <img src="{{ $qrUrl }}" 
                                                 alt="QR Hoàn tiền" 
                                                 class="img-thumbnail shadow-sm"
                                                 style="width: 120px; height: 120px; object-fit: contain; background: #fff;">
                                        </a>
                                        <div style="font-size: 0.6rem;" class="mt-1 text-primary font-weight-bold">QUÉT ĐỂ CHUYỂN KHOẢN</div>
                                    </div>
                                @else
                                    <div class="bg-light p-3 rounded small text-muted font-italic">N/A</div>
                                @endif
                            </td>
                            <td class="align-middle text-center">
                                @if($order->account_number)
                                <form action="{{ route('admin.orders.updateStatus', $order->id) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="status" value="refunded">
                                    <input type="hidden" name="note" value="Đã hoàn trả {{ number_format($order->total_price) }}đ qua {{ $order->bank_name }}">
                                    
                                    <button type="submit" class="btn btn-success btn-sm btn-block mb-2 shadow-sm" 
                                            onclick="return confirm('Hệ thống sẽ ghi nhận bạn ĐÃ CHUYỂN KHOẢN thành công số tiền {{ number_format($order->total_price) }}đ. Tiếp tục?')">
                                        <i class="fas fa-check-circle mr-1"></i> Xác nhận đã hoàn
                                    </button>
                                </form>
                                @endif
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-outline-info btn-sm btn-block">
                                    <i class="fas fa-eye mr-1"></i> Chi tiết đơn
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="fas fa-check-double fa-3x text-light mb-3"></i>
                                <p class="text-muted">Tuyệt vời! Không có đơn hàng nào cần hoàn tiền.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $orders->links() }}
            </div>
        </div>
    </div>
</div>

<style>
    .align-middle { vertical-align: middle !important; }
    .table-hover tbody tr:hover { background-color: rgba(78, 115, 223, 0.05); }
    .fw-bold { font-weight: 700; }
    .qr-wrapper img { transition: transform .2s ease-in-out; cursor: zoom-in; }
    .qr-wrapper img:hover { transform: scale(1.1); z-index: 10; position: relative; }
    .img-thumbnail { border-radius: 8px; border: 1px solid #dee2e6; padding: 5px; }
    .border-left-warning { border-left: .25rem solid #f6c23e !important; }
    .badge-info { background-color: #36b9cc; color: #fff; }
</style>

<script>
    function copyToClipboard(text) {
        if (!text) return;
        navigator.clipboard.writeText(text).then(() => {
            alert('Đã sao chép số tài khoản: ' + text);
        }).catch(err => {
            console.error('Lỗi khi copy: ', err);
        });
    }
</script>
@endsection