@extends('admin.layout.admin_layout')

@section('title')
    Chi tiết đơn hàng #{{ $order->id }}
@endsection

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Chi tiết đơn hàng #{{ $order->id }}</h1>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm"></i> Quay lại danh sách
        </a>
    </div>

    {{-- Thông báo --}}
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

    {{-- 1. KHU VỰC XỬ LÝ KHIẾU NẠI (TRẢ HÀNG) --}}
    @if(in_array($order->status, ['returning', 'returning_confirmed', 'returned', 'refunding', 'refunded']))
        <div class="card shadow mb-4 border-left-warning">
            <div class="card-header py-3 bg-warning text-white d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold"><i class="fas fa-exclamation-circle mr-1"></i> Thông tin khiếu nại / Trả hàng</h6>
                @if($order->status == 'returning')
                    <span class="badge badge-danger">Yêu cầu mới</span>
                @elseif($order->status == 'returning_confirmed')
                    <span class="badge badge-primary">Đã chấp nhận - Chờ nhận hàng</span>
                @endif
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Lý do khách hàng đưa ra:</strong></p>
                        <div class="p-3 bg-light rounded border mb-3 text-dark">
                            {{ $order->return_reason ?? 'Không có lý do chi tiết' }}
                        </div>
                        
                        <p><strong>Thông tin hoàn tiền:</strong></p>
                        <div class="p-3 bg-gray-100 rounded border mb-3 small">
                            <div><strong>Ngân hàng:</strong> {{ $order->bank_name ?? 'N/A' }}</div>
                            <div><strong>STK:</strong> {{ $order->account_number ?? 'N/A' }}</div>
                            <div><strong>Chủ tài khoản:</strong> {{ strtoupper($order->account_holder ?? 'N/A') }}</div>
                        </div>

                        @if($order->status == 'returning')
                            <div class="alert alert-info small">
                                <i class="fas fa-info-circle"></i> <strong>Hành động:</strong> Bấm "Chấp nhận" đơn hàng sẽ chuyển sang <b>Danh sách hoàn tiền</b> để tiếp tục xử lý.
                            </div>
                        @endif
                    </div>
                    <div class="col-md-6 text-center border-left">
                        <p><strong>Hình ảnh minh chứng:</strong></p>
                        
                        @php
                            $rawPath = ltrim($order->return_image, '/');
                            $imgSrc = asset('backend/img/no-image.png');

                            if ($rawPath) {
                                if (filter_var($rawPath, FILTER_VALIDATE_URL)) {
                                    $imgSrc = $rawPath;
                                } else {
                                    $storageSubPath = str_contains($rawPath, 'returns/') ? $rawPath : 'returns/' . $rawPath;
                                    $storagePathFull = 'storage/' . $storageSubPath;
                                    $uploadsPathFull = 'uploads/product/' . $storageSubPath;

                                    if (file_exists(public_path($storagePathFull))) {
                                        $imgSrc = asset($storagePathFull);
                                    } elseif (file_exists(public_path($uploadsPathFull))) {
                                        $imgSrc = asset($uploadsPathFull);
                                    } else {
                                        $imgSrc = asset('storage/' . ltrim(str_replace('storage/', '', $rawPath), '/'));
                                    }
                                }
                            }
                        @endphp
                        
                        @if($order->return_image)
                            <a href="{{ $imgSrc }}" target="_blank" title="Xem ảnh lớn">
                                <img src="{{ $imgSrc }}" 
                                     class="img-fluid rounded shadow-sm border" 
                                     style="max-height: 250px; width: auto; object-fit: contain; background: #fff;"
                                     onerror="this.onerror=null; this.src='https://placehold.co/600x400?text=Anh+Minh+Chung';">
                            </a>
                            <div class="mt-2 text-center">
                                <small class="text-muted"><i class="fas fa-search-plus"></i> Click để phóng to</small>
                            </div>
                        @else
                            <div class="py-5 text-muted font-italic bg-light rounded border">
                                <i class="fas fa-image-slash d-block fa-3x mb-2"></i>
                                Không có ảnh minh chứng
                            </div>
                        @endif
                    </div>
                </div>

                @if($order->status == 'returning')
                    <hr>
                    <div class="d-flex justify-content-end">
                        <form action="{{ route('admin.orders.updateStatus', $order->id) }}" method="POST" class="mr-2">
                            @csrf 
                            <input type="hidden" name="status" value="success">
                            <input type="hidden" name="note" value="Quản trị viên đã từ chối yêu cầu khiếu nại.">
                            <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Từ chối khiếu nại và đánh dấu đơn hàng thành công?')">Từ chối khiếu nại</button>
                        </form>
                        {{-- CẬP NHẬT: Chuyển sang returning_confirmed để đẩy qua danh sách hoàn tiền --}}
                        <form action="{{ route('admin.orders.updateStatus', $order->id) }}" method="POST">
                            @csrf 
                            <input type="hidden" name="status" value="returning_confirmed">
                            <input type="hidden" name="note" value="Đã chấp nhận khiếu nại. Chờ khách gửi hàng hoàn và xác nhận hoàn tiền.">
                            <button type="submit" class="btn btn-success px-4 shadow-sm" onclick="return confirm('Xác nhận chấp nhận khiếu nại? Đơn sẽ chuyển sang danh sách hoàn tiền.')">
                                <i class="fas fa-check mr-1"></i> Chấp nhận & Chuyển hoàn
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <div class="row">
        {{-- 2. THÔNG TIN GIAO HÀNG --}}
        <div class="col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Thông tin đơn hàng</h6>
                    <span class="badge badge-{{ $order->status_color ?? 'primary' }} px-3 py-2">
                        {{ strtoupper($order->status_label ?? $order->status) }}
                    </span>
                </div>
                <div class="card-body text-dark">
                    <div class="row mb-3">
                        <div class="col-sm-4 text-gray-600 font-weight-bold">Khách hàng:</div>
                        <div class="col-sm-8">{{ $order->customer_name ?? ($order->user->name ?? 'N/A') }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-gray-600 font-weight-bold">Số điện thoại:</div>
                        <div class="col-sm-8">{{ $order->phone_number }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-gray-600 font-weight-bold">Địa chỉ nhận hàng:</div>
                        <div class="col-sm-8">{{ $order->shipping_address }}</div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-sm-4 text-info font-weight-bold">Thanh toán:</div>
                        <div class="col-sm-8 text-uppercase">
                            {{ $order->payment_method }} 
                            @if($order->payment_status == 'paid')
                                <span class="badge badge-success ml-2">Đã thanh toán</span>
                            @elseif($order->payment_status == 'refunded')
                                <span class="badge badge-secondary ml-2">Đã hoàn tiền</span>
                            @else
                                <span class="badge badge-warning ml-2">Chưa thanh toán</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. CẬP NHẬT TRẠNG THÁI --}}
        <div class="col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Xử lý trạng thái</h6>
                </div>
                <div class="card-body">
                    {{-- Không khóa đơn khi ở trạng thái returning_confirmed --}}
                    @if(in_array($order->status, ['refunded', 'canceled']))
                        <div class="alert alert-secondary text-center">
                            <i class="fas fa-lock mr-2"></i> Đơn hàng này đã đóng.
                        </div>
                    @elseif($order->status == 'returning')
                         <div class="alert alert-warning text-center">
                            <i class="fas fa-hourglass-half mr-2"></i> Vui lòng xử lý khiếu nại ở khung bên trên.
                        </div>
                    @else
                        <form action="{{ route('admin.orders.updateStatus', $order->id) }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label class="small font-weight-bold">Chuyển sang trạng thái:</label>
                                <select name="status" class="form-control" required>
                                    <option value="" disabled selected>-- Chọn trạng thái --</option>
                                    
                                    @if($order->status == 'pending')
                                        <option value="confirmed">Xác nhận đơn hàng</option>
                                        <option value="canceled">Hủy đơn hàng</option>
                                    @endif

                                    @if(in_array($order->status, ['confirmed', 'paid']))
                                        <option value="shipping">Bắt đầu giao hàng</option>
                                        <option value="canceled">Hủy đơn hàng</option>
                                    @endif

                                    @if($order->status == 'shipping')
                                        <option value="success">Giao hàng thành công</option>
                                        <option value="canceled">Giao hàng thất bại (Hủy)</option>
                                    @endif

                                    {{-- Bổ sung cho phép xác nhận nhận hàng từ trang chi tiết nếu đơn đang ở trạng thái chờ hoàn --}}
                                    @if($order->status == 'returning_confirmed')
                                        <option value="returned">Xác nhận đã nhận hàng hoàn</option>
                                        <option value="refunding">Chuyển sang Đang hoàn tiền</option>
                                    @endif

                                    @if($order->status == 'returned' || $order->status == 'refunding')
                                        <option value="refunded">Xác nhận Đã hoàn tiền (Kết thúc)</option>
                                    @endif
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="small font-weight-bold">Ghi chú xử lý:</label>
                                <textarea name="note" class="form-control" rows="2" placeholder="Ghi chú này sẽ hiển thị cho khách hàng xem..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block shadow-sm">
                                <i class="fas fa-save mr-1"></i> Cập nhật hệ thống
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- 4. DANH SÁCH SẢN PHẨM --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-light">
            <h6 class="m-0 font-weight-bold text-primary">Sản phẩm trong đơn hàng</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 text-dark">
                    <thead class="bg-light">
                        <tr>
                            <th class="pl-4">Sản phẩm</th>
                            <th class="text-center">Số lượng</th>
                            <th class="text-right">Đơn giá</th>
                            <th class="text-right pr-4">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                        <tr>
                            <td class="pl-4">
                                <div class="font-weight-bold text-dark">{{ $item->product->name ?? 'Sản phẩm không còn tồn tại' }}</div>
                                <small class="text-muted">ID Sản phẩm: #{{ $item->product_id }}</small>
                            </td>
                            <td class="text-center">x{{ $item->quantity }}</td>
                            <td class="text-right">{{ number_format($item->price, 0, ',', '.') }} đ</td>
                            <td class="text-right pr-4 font-weight-bold text-primary">
                                {{ number_format($item->price * $item->quantity, 0, ',', '.') }} đ
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-100">
                            <td colspan="3" class="text-right font-weight-bold">TỔNG CỘNG THANH TOÁN:</td>
                            <td class="text-right pr-4 text-danger font-weight-bold h5">
                                {{ number_format($order->total_price, 0, ',', '.') }} đ
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- 5. LỊCH SỬ XỬ LÝ --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Nhật ký xử lý đơn hàng</h6>
        </div>
        <div class="card-body">
            <div class="timeline-small">
                @forelse($order->statusHistories->sortByDesc('created_at') as $history)
                    <div class="pb-3 pl-4 border-left position-relative">
                        <i class="fas fa-dot-circle text-primary position-absolute" style="left: -7px; top: 0; background: white; font-size: 12px;"></i>
                        <div class="font-weight-bold text-dark">{{ strtoupper(App\Models\Order::$statuses[$history->to_status] ?? $history->to_status) }}</div>
                        <small class="text-muted">
                            <i class="far fa-clock"></i> {{ $history->created_at->format('H:i d/m/Y') }} 
                            - <i class="far fa-user"></i> {{ $history->user->name ?? 'Hệ thống' }}
                        </small>
                        @if($history->note)
                            <div class="small font-italic text-gray-700 mt-1 p-2 bg-light rounded border-left-primary">
                                "{{ $history->note }}"
                            </div>
                        @endif
                    </div>
                @empty
                    <p class="text-center text-muted">Chưa có dữ liệu lịch sử cho đơn hàng này.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<style>
    .border-left { border-left: 2px solid #e3e6f0 !important; }
    .timeline-small div:last-child { border-left: 2px solid transparent !important; }
    .table td, .table th { vertical-align: middle; }
    .border-left-primary { border-left: 3px solid #4e73df !important; }
    .bg-gray-100 { background-color: #f8f9fc; }
</style>
@endsection