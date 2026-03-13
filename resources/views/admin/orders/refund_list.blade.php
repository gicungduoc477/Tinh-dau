@extends('admin.layout.admin_layout')

@section('title', 'Danh sách chờ hoàn tiền')

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Danh sách xử lý hoàn tiền</h1>
        <div class="alert alert-warning mb-0 py-2 shadow-sm border-left-warning">
            <i class="fas fa-wallet mr-2"></i> Tổng tiền cần hoàn: 
            <strong class="text-danger">{{ number_format($orders->sum('total_price'), 0, ',', '.') }}đ</strong>
        </div>
    </div>
    
    <div class="alert alert-info shadow-sm mb-4">
        <i class="fas fa-lightbulb mr-2"></i> <strong>Mẹo:</strong> Sử dụng App Ngân hàng quét mã QR để tự động điền <strong>số tiền</strong> và <strong>nội dung</strong> chuyển khoản chính xác 100%.
    </div>

    {{-- Thông báo --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                    <thead class="thead-light text-dark">
                        <tr>
                            <th width="8%">Mã đơn</th>
                            <th width="20%">Khách hàng & Trạng thái</th>
                            <th width="12%">Số tiền hoàn</th>
                            <th width="20%">Thông tin ngân hàng</th>
                            <th width="150px" class="text-center">Mã QR (Quét để trả)</th>
                            <th>Xử lý nhanh</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                        <tr>
                            <td class="align-middle fw-bold text-primary">#{{ $order->id }}</td>
                            <td class="align-middle">
                                <div class="font-weight-bold text-dark">{{ $order->customer_name }}</div>
                                <small class="text-muted"><i class="fas fa-phone fa-sm"></i> {{ $order->phone_number }}</small>
                                <div class="mt-2">
                                    @if($order->status == 'returning_confirmed')
                                        <span class="badge badge-warning"><i class="fas fa-truck-loading mr-1"></i> Chờ khách gửi hàng hoàn</span>
                                    @elseif($order->status == 'returned')
                                        <span class="badge badge-primary"><i class="fas fa-box-open mr-1"></i> Đã nhận hàng - Chờ hoàn tiền</span>
                                    @elseif($order->status == 'refunding')
                                        <span class="badge badge-info"><i class="fas fa-spinner fa-spin mr-1"></i> Đang thực hiện hoàn tiền</span>
                                    @endif
                                </div>
                            </td>
                            <td class="align-middle text-center">
                                <span class="text-danger font-weight-bold" style="font-size: 1.1rem;">
                                    {{ number_format($order->total_price, 0, ',', '.') }}đ
                                </span>
                            </td>
                            <td class="align-middle bg-light">
                                @if($order->account_number)
                                    <div class="p-1">
                                        <span class="badge badge-dark mb-1">{{ strtoupper($order->bank_name) }}</span><br>
                                        <span class="text-primary font-weight-bold" style="letter-spacing: 1px;">{{ $order->account_number }}</span>
                                        <button class="btn btn-sm btn-link p-0 ml-1" onclick="copyToClipboard('{{ $order->account_number }}')" title="Sao chép STK">
                                            <i class="far fa-copy text-secondary"></i>
                                        </button><br>
                                        <small class="text-uppercase text-dark font-weight-bold">{{ $order->account_holder }}</small>
                                    </div>
                                @else
                                    <div class="text-center text-muted small"><em>Thiếu thông tin nhận tiền</em></div>
                                @endif
                            </td>
                            <td class="text-center align-middle">
                                @if($order->account_number)
                                    @php
                                        $bankList = [
                                            'momo' => '970422', 'mbbank' => '970422', 'mb' => '970422',
                                            'vietcombank' => '970436', 'vcb' => '970436',
                                            'vietinbank' => '970415', 'acb' => '970416',
                                            'bidv' => '970418', 'agribank' => '970405',
                                            'tpbank' => '970423', 'techcombank' => '970407', 'tcb' => '970407'
                                        ];
                                        $inputBank = strtolower(trim($order->bank_name));
                                        $bankId = $bankList[$inputBank] ?? '970422';
                                        $amount = intval($order->total_price);
                                        $description = 'Hoan tien don ' . $order->id;
                                        $qrUrl = "https://img.vietqr.io/image/{$bankId}-{$order->account_number}-compact.png?amount={$amount}&addInfo=" . urlencode($description) . "&accountName=" . urlencode($order->account_holder);
                                    @endphp
                                    
                                    <div class="qr-wrapper">
                                        <a href="{{ $qrUrl }}" target="_blank" title="Xem ảnh lớn">
                                            <img src="{{ $qrUrl }}" 
                                                 alt="QR Hoàn tiền" 
                                                 class="img-thumbnail shadow-sm"
                                                 style="width: 110px; height: 110px; object-fit: contain; background: #fff;">
                                        </a>
                                    </div>
                                @else
                                    <span class="text-muted small">N/A</span>
                                @endif
                            </td>
                            <td class="align-middle text-center">
                                {{-- Nút 1: Xác nhận nhận được hàng hoàn (Nếu đang ở trạng thái returning_confirmed) --}}
                                @if($order->status == 'returning_confirmed')
                                    <form action="{{ route('admin.orders.updateStatus', $order->id) }}" method="POST" class="mb-2">
                                        @csrf
                                        <input type="hidden" name="status" value="returned">
                                        <input type="hidden" name="note" value="Đã xác nhận nhận được hàng hoàn từ khách. Chờ hoàn tiền.">
                                        <button type="submit" class="btn btn-primary btn-sm btn-block shadow-sm">
                                            <i class="fas fa-box mr-1"></i> Đã nhận được hàng
                                        </button>
                                    </form>
                                @endif

                                {{-- Nút 2: Xác nhận đã chuyển tiền hoàn (Nếu đã nhận hàng hoặc đang refunding) --}}
                                @if(in_array($order->status, ['returned', 'refunding']))
                                    <form action="{{ route('admin.orders.updateStatus', $order->id) }}" method="POST" class="mb-2">
                                        @csrf
                                        <input type="hidden" name="status" value="refunded">
                                        <input type="hidden" name="note" value="Đã hoàn trả {{ number_format($order->total_price) }}đ qua ngân hàng {{ $order->bank_name }}. Giao dịch kết thúc.">
                                        <button type="submit" class="btn btn-success btn-sm btn-block shadow-sm" 
                                                onclick="return confirm('Xác nhận bạn đã chuyển khoản thành công?')">
                                            <i class="fas fa-check-double mr-1"></i> Xác nhận ĐÃ HOÀN TIỀN
                                        </button>
                                    </form>
                                @endif

                                <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-outline-info btn-sm btn-block">
                                    <i class="fas fa-eye mr-1"></i> Xem chi tiết
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-check-circle fa-3x text-light mb-3"></i>
                                    <p>Hiện tại không có đơn hàng nào cần xử lý hoàn tiền.</p>
                                </div>
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
    .table-hover tbody tr:hover { background-color: rgba(78, 115, 223, 0.03); }
    .fw-bold { font-weight: 700; }
    .qr-wrapper img { transition: transform .2s ease-in-out; cursor: zoom-in; }
    .qr-wrapper img:hover { transform: scale(1.1); z-index: 10; position: relative; }
    .img-thumbnail { border-radius: 8px; border: 1px solid #dee2e6; }
    .border-left-warning { border-left: .25rem solid #f6c23e !important; }
    .badge { padding: 0.5em 0.8em; font-size: 85%; }
</style>

<script>
    function copyToClipboard(text) {
        if (!text) return;
        navigator.clipboard.writeText(text).then(() => {
            // Hiển thị toast hoặc alert nhỏ
            const btn = event.currentTarget;
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check text-success"></i>';
            setTimeout(() => { btn.innerHTML = originalHtml; }, 2000);
        }).catch(err => {
            console.error('Lỗi khi copy: ', err);
        });
    }
</script>
@endsection