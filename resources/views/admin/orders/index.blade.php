@extends('admin.layout.admin_layout')

@section('title')
    {{ request('status') ? 'Đơn hàng: ' . App\Models\Order::$statuses[request('status')] : 'Tất cả đơn hàng' }}
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-list mr-2"></i>{{ request('status') ? 'Đơn hàng ' . App\Models\Order::$statuses[request('status')] : 'Danh sách đơn hàng' }}
        </h1>
        @if(request('status') || request('search'))
            <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-secondary shadow-sm">
                <i class="fas fa-redo fa-sm"></i> Hiện tất cả
            </a>
        @endif
    </div>

    {{-- Hiển thị thông báo Success/Error từ Controller --}}
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

    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-light d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Thông tin bảng đơn hàng</h6>
            <form action="{{ route('admin.orders.index') }}" method="GET" class="form-inline">
                <input type="text" name="search" class="form-control form-control-sm mr-2" placeholder="Tìm mã đơn, tên, SĐT..." value="{{ request('search') }}">
                <button type="submit" class="btn btn-sm btn-primary">Tìm kiếm</button>
            </form>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                    <thead class="thead-light text-center">
                        <tr>
                            <th>Mã đơn</th>
                            <th>Khách hàng</th>
                            <th>SL</th>
                            <th>Tổng tiền</th>
                            <th>Thanh toán</th>
                            <th>Trạng thái</th>
                            <th>Ngày tạo</th>
                            <th width="120">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                        <tr>
                            <td class="text-center font-weight-bold text-dark">#{{ $order->id }}</td>
                            <td>
                                <div class="font-weight-bold text-dark">{{ $order->name ?? ($order->user->name ?? 'Khách lẻ') }}</div>
                                <small class="text-muted"><i class="fas fa-phone fa-xs mr-1"></i>{{ $order->phone_number }}</small>
                            </td>
                            <td class="text-center">{{ $order->items_count }}</td>
                            <td class="text-right font-weight-bold text-danger">
                                {{ number_format($order->total_price) }} đ
                            </td>
                            <td class="text-center small">
                                <span class="text-uppercase font-weight-bold text-xs">{{ $order->payment_method }}</span><br>
                                <span class="badge badge-{{ $order->payment_status === 'paid' ? 'success' : 'light border' }}">
                                    {{ $order->payment_status === 'paid' ? 'Đã thu' : 'Chưa thu' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-{{ $order->status_color }} px-2 py-1 shadow-sm" style="min-width: 90px;">
                                    {{ $order->status_label }}
                                </span>
                            </td>
                            <td class="text-center small">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <a href="{{ route('admin.orders.show', $order->id) }}" 
                                       class="btn btn-sm btn-info shadow-sm" 
                                       title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    {{-- Chỉ cho phép đổi trạng thái nếu đơn hàng chưa đóng (canceled/returned/refunded) --}}
                                    @if(!in_array($order->status, ['canceled', 'returned', 'refunded']))
                                        <button type="button" 
                                                class="btn btn-sm btn-success shadow-sm ml-1" 
                                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                                            <h6 class="dropdown-header">Chuyển trạng thái tiếp theo:</h6>
                                            
                                            @php
                                                // Định nghĩa lại flow đơn giản để hiển thị ở View
                                                $nextSteps = [
                                                    'pending'   => ['confirmed', 'paid', 'canceled'],
                                                    'paid'      => ['confirmed', 'shipping', 'canceled'],
                                                    'confirmed' => ['shipping', 'canceled'],
                                                    'shipping'  => ['success', 'canceled'],
                                                    'success'   => ['returning', 'returned'],
                                                    'returning' => ['returned', 'success'],
                                                ];
                                                $availableSteps = $nextSteps[$order->status] ?? [];
                                            @endphp

                                            @foreach($availableSteps as $stepCode)
                                                <form action="{{ route('admin.orders.updateStatus', $order->id) }}" method="POST" 
                                                      onsubmit="return confirm('Xác nhận chuyển đơn hàng sang trạng thái: {{ App\Models\Order::$statuses[$stepCode] }}?')">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="status" value="{{ $stepCode }}">
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="fas fa-arrow-right fa-xs mr-1 text-muted"></i>
                                                        {{ App\Models\Order::$statuses[$stepCode] }}
                                                    </button>
                                                </form>
                                            @endforeach

                                            @if(empty($availableSteps))
                                                <span class="dropdown-item disabled">Không có thao tác khả dụng</span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fas fa-folder-open fa-3x mb-3 d-block"></i>
                                Không tìm thấy đơn hàng nào.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4 d-flex justify-content-center">
                {{ $orders->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>

<style>
    .dropdown-item { cursor: pointer; }
    .dropdown-item:hover { background-color: #f8f9fc; color: #4e73df; }
    .badge { font-weight: 500; }
</style>
@endsection