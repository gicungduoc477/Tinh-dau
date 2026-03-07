@extends('admin.layout.admin_layout')

@section('title', 'Bảng điều khiển')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow h-100 py-2 border-left-primary">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Người dùng hệ thống</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $users_count ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-primary border-0 p-0 font-weight-bold">Xem danh sách <i class="fas fa-arrow-right ml-1"></i></a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow h-100 py-2 border-left-success">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Sản phẩm trong kho</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ isset($products) ? $products->total() : 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-box fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        <a href="{{ route('admin.product.index') }}" class="btn btn-sm btn-outline-success border-0 p-0 font-weight-bold">Quản lý kho <i class="fas fa-arrow-right ml-1"></i></a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow h-100 py-2 border-left-warning bg-gradient-light">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Doanh thu thực tế</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($total_revenue ?? 0) }}đ</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-wallet fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <div class="mt-2 text-warning small font-italic"><i class="fas fa-check-circle"></i> Đã trừ đơn hoàn/hủy</div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow h-100 py-2 border-left-danger">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Đơn hủy/Hoàn trả</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ \App\Models\Order::whereIn('status', ['canceled', 'returned', 'refunded', 'Hủy đơn', 'Hoàn hàng'])->count() }} đơn
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-sync-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <div class="mt-2 text-danger small">Cần xử lý hoàn tiền</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-white d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-chart-line mr-2"></i>Biểu đồ doanh thu 6 tháng gần nhất (Đã xác nhận)</h6>
                    <div class="dropdown no-arrow">
                        <span class="badge badge-primary-soft text-primary p-2">Đơn vị: VNĐ</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-area" style="height: 320px;">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-white border-bottom">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-box-open mr-2"></i>Sản phẩm mới cập nhật</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th class="px-3 border-0">Ảnh</th>
                                    <th class="border-0">Tên sản phẩm</th>
                                    <th class="border-0">Giá</th>
                                    <th class="border-0 text-center">Kho</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($products as $p)
                                <tr>
                                    <td class="px-3">
                                        <img src="{{ (!empty($p->image) && file_exists(public_path('uploads/product/' . $p->image))) 
                                            ? asset('uploads/product/' . $p->image) 
                                            : asset('backend/img/no-image.png') }}" 
                                             class="img-thumbnail" style="width: 40px; height: 40px; object-fit: cover;">
                                    </td>
                                    <td>
                                        <div class="font-weight-bold text-dark">{{ Str::limit($p->name, 25) }}</div>
                                        <small class="text-muted">{{ $p->classification }}</small>
                                    </td>
                                    <td class="text-danger font-weight-bold">{{ number_format($p->price) }}đ</td>
                                    <td class="text-center">
                                        <span class="badge {{ $p->stock > 5 ? 'badge-success' : 'badge-warning' }}">
                                            {{ $p->stock }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center py-4">Chưa có sản phẩm nào.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-white border-bottom">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-user-plus mr-2"></i>Người dùng mới</h6>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse($recent_users as $u)
                        <li class="list-group-item d-flex align-items-center py-3">
                            <div class="mr-3">
                                <div class="icon-circle {{ $u->role == 'admin' ? 'bg-danger' : 'bg-primary' }} text-white shadow-sm">
                                    <i class="fas fa-user"></i>
                                </div>
                            </div>
                            <div style="flex: 1">
                                <div class="font-weight-bold text-dark">{{ $u->name }}</div>
                                <div class="small text-muted text-truncate" style="max-width: 150px;">{{ $u->email }}</div>
                            </div>
                            <div class="text-right">
                                <span class="badge badge-light border text-muted" style="font-size: 10px;">{{ $u->created_at->diffForHumans() }}</span>
                            </div>
                        </li>
                        @empty
                        <li class="list-group-item text-center py-4 text-muted">Chưa có thành viên mới.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('revenueChart').getContext('2d');
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($months ?? []) !!},
                datasets: [{
                    label: "Doanh thu",
                    borderColor: "#4e73df",
                    backgroundColor: "rgba(78, 115, 223, 0.05)",
                    pointRadius: 4,
                    pointBackgroundColor: "#4e73df",
                    pointBorderColor: "#fff",
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: "#4e73df",
                    pointHoverBorderColor: "#fff",
                    pointHitRadius: 10,
                    pointBorderWidth: 2,
                    data: {!! json_encode($totals ?? []) !!},
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: "rgb(255,255,255)",
                        bodyColor: "#858796",
                        titleMarginBottom: 10,
                        titleColor: '#6e707e',
                        titleFont: { size: 14, weight: 'bold' },
                        borderColor: '#dddfeb',
                        borderWidth: 1,
                        xPadding: 15,
                        yPadding: 15,
                        displayColors: false,
                        intersect: false,
                        mode: 'index',
                        caretPadding: 10,
                        callbacks: {
                            label: function(context) {
                                return 'Doanh thu thuần: ' + context.parsed.y.toLocaleString('vi-VN') + ' ₫';
                            }
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false, drawBorder: false }, ticks: { font: { size: 11 } } },
                    y: {
                        ticks: {
                            maxTicksLimit: 5,
                            padding: 10,
                            font: { size: 11 },
                            callback: function(value) {
                                if (value >= 1000000) return (value / 1000000) + 'M ₫';
                                return value.toLocaleString('vi-VN') + ' ₫';
                            }
                        },
                        grid: { color: "rgb(234, 236, 244)", drawBorder: false, borderDash: [2] }
                    }
                }
            }
        });
    });
</script>

<style>
    .icon-circle { height: 40px; width: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
    .img-thumbnail { border-radius: 8px; border: 1px solid #f1f1f1; transition: transform .2s; }
    .img-thumbnail:hover { transform: scale(1.1); }
    .card { border: none; transition: all 0.3s ease; }
    .card:hover { transform: translateY(-3px); }
    .badge-primary-soft { background-color: #eef2ff; color: #4e73df; }
    .border-left-primary { border-left: 4px solid #4e73df !important; }
    .border-left-success { border-left: 4px solid #1cc88a !important; }
    .border-left-warning { border-left: 4px solid #f6c23e !important; }
    .border-left-danger { border-left: 4px solid #e74a3b !important; }
</style>
@endsection