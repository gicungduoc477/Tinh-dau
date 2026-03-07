@extends('admin.layout.admin_layout')

@section('title')
    Chi tiết đơn hàng #{{ $order->id }}
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Chi tiết đơn hàng #{{ $order->id }}</h1>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm"></i> Quay lại danh sách
        </a>
    </div>

    {{-- Hiển thị thông báo --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-exclamation-triangle mr-2"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    {{-- KHU VỰC CẢNH BÁO HOÀN TIỀN --}}
    @if(method_exists($order, 'needsRefund') && $order->needsRefund())
        <div class="card shadow mb-4 border-left-danger bg-light">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-1 text-center">
                        <i class="fas fa-money-bill-wave fa-2x text-danger"></i>
                    </div>
                    <div class="col-md-8">
                        <h5 class="font-weight-bold text-danger mb-1">Yêu cầu Hoàn tiền Online!</h5>
                        <p class="mb-0">Khách hàng đã thanh toán qua <strong>{{ strtoupper($order->payment_method) }}</strong>. 
                        Mã giao dịch: <code class="bg-white px-2 py-1 border">{{ $order->transaction_id }}</code></p>
                        <small class="text-muted italic">* Vui lòng thực hiện hoàn tiền trên cổng thanh toán trước khi đánh dấu "Đã hoàn tiền".</small>
                    </div>
                    <div class="col-md-3 text-right">
                        @if($order->status !== 'refunded')
                        {{-- FIX: Đã đồng bộ POST và xóa @method('PUT') --}}
                        <form action="{{ route('admin.orders.updateStatus', $order->id) }}" method="POST">
                            @csrf 
                            <input type="hidden" name="status" value="refunded">
                            <input type="hidden" name="note" value="Xác nhận đã hoàn tiền qua cổng thanh toán.">
                            <button type="submit" class="btn btn-danger btn-sm shadow-sm" onclick="return confirm('Bạn chắc chắn đã hoàn tiền trên cổng thanh toán?')">
                                <i class="fas fa-check"></i> Xác nhận đã hoàn tiền
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- KHU VỰC XỬ LÝ KHIẾU NẠI --}}
    @if($order->status == 'returning')
        <div class="card shadow mb-4 border-left-warning">
            <div class="card-header py-3 bg-warning text-white">
                <h6 class="m-0 font-weight-bold"><i class="fas fa-exclamation-circle mr-1"></i> Yêu cầu khiếu nại / Trả hàng từ khách hàng</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Lý do khách hàng đưa ra:</strong></p>
                        <div class="p-3 bg-light rounded border mb-3">
                            {{ $order->return_reason ?? 'Không có lý do cụ thể.' }}
                        </div>
                        <div class="alert alert-info small">
                            <i class="fas fa-info-circle"></i> <strong>Lưu ý:</strong> Chấp nhận trả hàng sẽ chuyển sang trạng thái <strong>"Đã trả hàng"</strong> và tự động hoàn hàng vào kho.
                        </div>
                    </div>
                    <div class="col-md-6 text-center border-left">
                        <p><strong>Hình ảnh minh chứng:</strong></p>
                        @if($order->return_image)
                            <a href="{{ asset('storage/' . $order->return_image) }}" target="_blank">
                                <img src="{{ asset('storage/' . $order->return_image) }}" class="img-fluid rounded shadow-sm border" style="max-height: 200px;">
                            </a>
                        @else
                            <div class="py-4 text-muted font-italic">Không có ảnh minh chứng</div>
                        @endif
                    </div>
                </div>
                <hr>
                <div class="d-flex justify-content-end">
                    {{-- FIX: Đồng bộ POST cho xử lý khiếu nại --}}
                    <form action="{{ route('admin.orders.updateStatus', $order->id) }}" method="POST" class="mr-2">
                        @csrf 
                        <input type="hidden" name="status" value="success">
                        <input type="hidden" name="note" value="Quản trị viên đã bác bỏ yêu cầu khiếu nại của khách hàng.">
                        <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Từ chối khiếu nại và giữ nguyên trạng thái Thành công?')">Từ chối khiếu nại</button>
                    </form>
                    <form action="{{ route('admin.orders.updateStatus', $order->id) }}" method="POST">
                        @csrf 
                        <input type="hidden" name="status" value="returned">
                        <input type="hidden" name="note" value="Quản trị viên đã chấp nhận và nhận hàng trả về.">
                        <button type="submit" class="btn btn-success px-4" onclick="return confirm('Xác nhận đã nhận hàng trả lại và hoàn sản phẩm vào kho?')">Chấp nhận & Nhận hàng trả</button>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        {{-- Cột bên trái: Thông tin khách hàng & Thanh toán --}}
        <div class="col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Thông tin chung</h6>
                    <span class="badge badge-{{ $order->status_color }} px-3 py-2 text-uppercase">
                        {{ $order->status_label }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-4 font-weight-bold">Khách hàng:</div>
                        <div class="col-sm-8 text-dark font-weight-bold">{{ $order->customer_name ?? $order->name }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 font-weight-bold">Số điện thoại:</div>
                        <div class="col-sm-8">{{ $order->phone_number }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 font-weight-bold">Địa chỉ giao:</div>
                        <div class="col-sm-8">{{ $order->shipping_address }}</div>
                    </div>
                    <hr>
                    <div class="row mb-3">
                        <div class="col-sm-4 font-weight-bold text-info">Thanh toán:</div>
                        <div class="col-sm-8">
                            <span class="text-uppercase font-weight-bold">{{ $order->payment_method ?? 'COD' }}</span>
                            @if($order->payment_status == 'paid')
                                <span class="badge badge-success ml-2"><i class="fas fa-check"></i> Đã thanh toán</span>
                                @if($order->paid_at)
                                    <div class="small text-muted mt-1">Vào lúc: {{ \Carbon\Carbon::parse($order->paid_at)->format('d/m/Y H:i') }}</div>
                                @endif
                            @else
                                <span class="badge badge-warning ml-2">Chờ thanh toán</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Cột bên phải: Form cập nhật trạng thái chính --}}
        <div class="col-lg-5">
            <div class="card shadow mb-4 border-left-primary">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Cập nhật tiến độ</h6>
                </div>
                <div class="card-body">
                    @php
                        $isClosed = in_array($order->status, ['returned', 'refunded', 'canceled']);
                    @endphp

                    @if($isClosed)
                        <div class="text-center py-3">
                            <i class="fas fa-lock fa-3x text-gray-300 mb-3"></i>
                            <p class="text-muted font-italic mb-0">Đơn hàng này đã kết thúc xử lý.</p>
                        </div>
                    @else
                        {{-- FIX: Form chính chuyển về POST và xóa @method('PUT') --}}
                        <form action="{{ route('admin.orders.updateStatus', $order->id) }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label class="small font-weight-bold">Chuyển sang trạng thái</label>
                                <select name="status" class="form-control border-primary shadow-sm" required>
                                    <option value="" selected disabled>-- Chọn trạng thái cập nhật --</option>

                                    @if($order->status == 'pending')
                                        <option value="confirmed">Xác nhận đơn hàng</option>
                                        <option value="paid">Xác nhận đã trả tiền (Chờ giao)</option>
                                        <option value="canceled">Hủy đơn hàng</option>
                                    @endif

                                    @if($order->status == 'paid')
                                        <option value="confirmed">Xác nhận đơn hàng</option>
                                        <option value="shipping">Bắt đầu giao hàng</option>
                                        <option value="canceled">Hủy đơn hàng (Cần hoàn tiền)</option>
                                    @endif

                                    @if($order->status == 'confirmed')
                                        <option value="shipping">Bắt đầu giao hàng</option>
                                        <option value="canceled">Hủy đơn hàng</option>
                                    @endif

                                    @if($order->status == 'shipping')
                                        <option value="success">Giao hàng thành công</option>
                                        <option value="canceled">Giao hàng thất bại (Hủy)</option>
                                    @endif

                                    @if($order->status == 'success')
                                        <option value="returning">Chuyển sang Khiếu nại (Thủ công)</option>
                                    @endif

                                    @if(in_array($order->status, ['returning', 'refunding']))
                                         <option value="refunded">Đã hoàn tiền thành công</option>
                                         <option value="returned">Đã nhận hàng trả lại</option>
                                    @endif
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="small font-weight-bold">Ghi chú nội bộ</label>
                                <textarea name="note" class="form-control" rows="2" placeholder="Lý do thay đổi, mã vận đơn, v.v."></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block shadow-sm">
                                <i class="fas fa-save fa-sm mr-1"></i> Lưu thay đổi
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Bảng danh sách sản phẩm --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Sản phẩm trong đơn hàng</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                    <thead class="bg-light">
                        <tr>
                            <th>Sản phẩm</th>
                            <th class="text-right">Giá bán</th>
                            <th class="text-center">Số lượng</th>
                            <th class="text-right">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                        <tr>
                            <td>
                                <div class="font-weight-bold text-dark">{{ $item->product->name ?? 'Sản phẩm đã xóa' }}</div>
                                <small class="text-muted">ID: #{{ $item->product_id }}</small>
                            </td>
                            <td class="text-right">{{ number_format($item->price) }} đ</td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td class="text-right font-weight-bold">{{ number_format($item->price * $item->quantity) }} đ</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-light">
                        <tr class="table-warning">
                            <td colspan="3" class="text-right font-weight-bold text-uppercase">Tổng thanh toán:</td>
                            <td class="text-right font-weight-bold text-danger h5 mb-0">
                                {{ number_format($order->total_price) }} đ
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- Lịch sử xử lý đơn hàng (Timeline) --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Lịch sử xử lý (Timeline)</h6>
        </div>
        <div class="card-body">
            @if($order->statusHistories && $order->statusHistories->count() > 0)
                <div class="timeline-small">
                    @foreach($order->statusHistories->sortByDesc('created_at') as $history)
                        <div class="mb-3 pl-4 style-border-left" style="border-left: 2px solid #e3e6f0; position: relative;">
                            <i class="fas fa-check-circle text-primary" style="position: absolute; left: -9px; top: 0; background: #fff;"></i>
                            <div class="font-weight-bold text-dark">
                                Chuyển sang: <span class="badge badge-light border">{{ \App\Models\Order::$statuses[$history->to_status] ?? $history->to_status }}</span>
                            </div>
                            <div class="small text-muted">
                                <i class="fas fa-user fa-sm mr-1"></i> {{ $history->user->name ?? 'Hệ thống' }} 
                                | <i class="fas fa-clock fa-sm mr-1"></i> {{ $history->created_at->format('d/m/Y H:i:s') }}
                            </div>
                            @if($history->note)
                                <div class="mt-1 p-2 bg-light rounded small border italic">
                                    "{{ $history->note }}"
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-center text-muted my-3">Chưa có dữ liệu lịch sử.</p>
            @endif
        </div>
    </div>
</div>

<style>
    .style-border-left:last-child {
        border-left: 0 !important;
    }
    .italic { font-style: italic; }
</style>
@endsection