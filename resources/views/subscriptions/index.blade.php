@extends('layouts.app')

@section('content')
<div class="container py-5" style="min-height: 600px;">
    <div class="row">
        <div class="col-md-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
                    <li class="breadcrumb-item active">Gói đăng ký định kỳ</li>
                </ol>
            </nav>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 text-primary"><i class="bi bi-arrow-repeat me-2"></i>Gói đăng ký định kỳ của tôi</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Sản phẩm</th>
                                    <th>Số lượng</th>
                                    <th>Chu kỳ</th>
                                    <th>Ngày giao tiếp theo</th>
                                    <th>Trạng thái</th>
                                    <th class="text-end pe-4">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($subscriptions as $sub)
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <img src="{{ asset('uploads/product/'.$sub->product->image) }}" 
                                                 alt="{{ $sub->product->name }}"
                                                 width="60" class="rounded border me-3">
                                            <div>
                                                <div class="fw-bold">{{ $sub->product->name }}</div>
                                                <small class="text-muted">Giá ưu đãi: {{ number_format($sub->product->price * 0.9) }}đ</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>x{{ $sub->quantity }}</td>
                                    <td>{{ $sub->interval_days }} ngày</td>
                                    <td>
                                        <span class="text-dark fw-medium">
                                            {{ \Carbon\Carbon::parse($sub->next_shipping_date)->format('d/m/Y') }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($sub->status == 'active')
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">Đang chạy</span>
                                        @else
                                            <span class="badge bg-light text-muted border px-3 py-2">Đã hủy</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        @if($sub->status == 'active')
                                        <form action="{{ route('subscriptions.cancel', $sub->id) }}" method="POST" 
                                              onsubmit="return confirm('Bạn có chắc chắn muốn dừng gói đăng ký này không?')">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Hủy đăng ký</button>
                                        </form>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <img src="https://cdn-icons-png.flaticon.com/512/11329/11329073.png" width="80" class="mb-3 opacity-50">
                                        <p class="text-muted">Bạn chưa có gói đăng ký định kỳ nào.</p>
                                        <a href="{{ route('products.index') }}" class="btn btn-primary btn-sm">Mua sắm ngay</a>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection