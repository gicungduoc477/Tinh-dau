@extends('layouts.app')

@section('title', 'Đơn hàng của tôi')

@section('content')
<div class="container mt-5">
    <div class="card shadow-sm p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Danh sách đơn hàng</h4>
            <a href="{{ route('home') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-shopping-cart"></i> Tiếp tục mua hàng
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="text-center">Mã</th>
                        <th>Ngày đặt</th>
                        <th class="text-center">Số lượng</th>
                        <th>Tổng tiền</th>
                        <th class="text-center">Trạng thái</th>
                        <th class="text-end">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $o)
                    <tr>
                        <td class="text-center fw-bold">#{{ $o->id }}</td>
                        <td>
                            <span class="text-muted small">
                                <i class="far fa-clock"></i> {{ $o->created_at->format('d/m/Y H:i') }}
                            </span>
                        </td>
                        <td class="text-center">{{ $o->items_count }}</td>
                        <td class="text-danger fw-bold">
                            {{ number_format($o->total_price, 0, ',', '.') }} đ
                        </td>
                        <td class="text-center">
                            {{-- Sử dụng Accessor từ Model Order để đồng bộ màu sắc và nhãn --}}
                            <span class="badge rounded-pill bg-{{ $o->status_color }} px-3 py-2">
                                {{ $o->status_label }}
                            </span>
                            
                            @if($o->payment_status === 'paid' && $o->status === 'pending')
                                <div class="mt-1">
                                    <small class="text-success small"><i class="fas fa-check-circle"></i> Đã thanh toán</small>
                                </div>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('orders.show', $o->id) }}" class="btn btn-sm btn-primary px-3 shadow-sm">
                                <i class="fas fa-eye"></i> Xem
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <img src="https://cdn-icons-png.flaticon.com/512/2038/2038854.png" width="80" class="mb-3 opacity-50">
                            <p class="text-muted">Bạn chưa có đơn hàng nào.</p>
                            <a href="{{ route('products.index') }}" class="btn btn-primary">Mua sắm ngay</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4 d-flex justify-content-center">
            {{ $orders->links() }}
        </div>
    </div>
</div>

<style>
    .badge { font-weight: 500; font-size: 0.85rem; }
    .table thead th { font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px; }
    .fw-bold { font-weight: 600 !important; }
</style>
@endsection