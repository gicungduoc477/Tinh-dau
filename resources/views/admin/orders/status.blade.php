@extends('admin.layout.admin_layout')

@section('title', 'Tổng quan Trạng thái Đơn hàng')

@section('content')
<div class="container-fluid mt-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Quản lý Trạng thái Đơn hàng</h1>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-list fa-sm text-white-50"></i> Xem tất cả đơn hàng
        </a>
    </div>

    <div class="row">
        {{-- Loop qua dữ liệu đã được xử lý từ Controller --}}
        @foreach($statusCounts as $key => $data)
            <div class="col-xl-3 col-md-6 mb-4"> {{-- Đổi col-xl-4 thành col-xl-3 để hiển thị 4 card mỗi dòng --}}
                <div class="card border-left-{{ $data['color'] }} shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-{{ $data['color'] }} text-uppercase mb-1">
                                    {{ $data['label'] }}
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ number_format($data['count']) }} Đơn hàng
                                </div>
                            </div>
                            <div class="col-auto">
                                @php
                                    $icons = [
                                        'pending'   => 'fa-clock',
                                        'paid'      => 'fa-credit-card', {{-- Icon cho thanh toán online --}}
                                        'confirmed' => 'fa-check-circle',
                                        'shipping'  => 'fa-truck',
                                        'success'   => 'fa-check-double',
                                        'returning' => 'fa-undo-alt',
                                        'returned'  => 'fa-box-open',
                                        'canceled'  => 'fa-times-circle'
                                    ];
                                    $icon = $icons[$key] ?? 'fa-clipboard-list';
                                @endphp
                                <i class="fas {{ $icon }} fa-2x text-gray-300"></i>
                            </div>
                        </div>
                        <div class="mt-3 d-flex justify-content-between align-items-center">
                            <a href="{{ route('admin.orders.index', ['status' => $key]) }}" 
                               class="btn btn-sm btn-{{ $data['color'] }} text-white">
                                <i class="fas fa-eye fa-sm"></i> Chi tiết
                            </a>
                            <small class="text-muted italic">Cập nhật: {{ now()->format('H:i') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Quy trình xử lý thực tế (State Machine Visual) --}}
    <div class="card shadow mb-4 mt-2">
        <div class="card-header py-3 bg-light">
            <h6 class="m-0 font-weight-bold text-secondary italic">
                <i class="fas fa-info-circle mr-1"></i> Luồng vận hành đơn hàng (PayOS Integrated)
            </h6>
        </div>
        <div class="card-body">
            <div class="row text-center">
                <div class="col">
                    <div class="badge badge-warning p-2">1. Chờ thanh toán</div>
                    <div class="mt-2 small text-muted">Pending</div>
                </div>
                <div class="col-auto mt-2"><i class="fas fa-arrow-right text-gray-400"></i></div>
                <div class="col">
                    <div class="badge badge-info p-2">2. Đã thanh toán</div>
                    <div class="mt-2 small text-muted">Paid (Auto)</div>
                </div>
                <div class="col-auto mt-2"><i class="fas fa-arrow-right text-gray-400"></i></div>
                <div class="col">
                    <div class="badge badge-primary p-2">3. Đang giao hàng</div>
                    <div class="mt-2 small text-muted">Shipping</div>
                </div>
                <div class="col-auto mt-2"><i class="fas fa-arrow-right text-gray-400"></i></div>
                <div class="col">
                    <div class="badge badge-success p-2">4. Hoàn tất</div>
                    <div class="mt-2 small text-muted">Success</div>
                </div>
            </div>
            
            <hr>
            <div class="small text-muted">
                <strong>Lưu ý:</strong> Khi khách hàng thanh toán qua PayOS, hệ thống sẽ tự động nhảy từ bước 1 sang bước 2. Admin bắt đầu xử lý từ bước 2 trở đi.
            </div>
        </div>
    </div>
</div>
@endsection