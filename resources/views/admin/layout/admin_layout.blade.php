<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Quản trị Hệ thống - @yield('title')</title>

    <link href="{{ asset('backend/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">

    <link href="{{ asset('backend/css/sb-admin-2.min.css') }}" rel="stylesheet">
    
    <style>
        .collapse-item.active { font-weight: bold; color: #4e73df !important; background-color: #f8f9fc; }
        .sidebar-brand-icon i { color: #f8f9fc; }
        .toast { min-width: 300px; border-radius: 8px; z-index: 9999; border: none; }
        .img-profile { object-fit: cover; border: 2px solid #e3e6f0; }
        .sidebar-heading { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; }
        .toast-header { border-bottom: none; }
        
        /* Tùy chỉnh badge cho nhỏ gọn và nổi bật */
        .sidebar .nav-item .badge-counter {
            position: absolute;
            right: 0.75rem;
            text-indent: 0;
            padding: 0.25em 0.5em;
            font-size: 0.65rem;
        }

        /* Hiệu ứng nhấp nháy cho các mục cần chú ý gấp (Khiếu nại/Hoàn tiền) */
        .animate-pulse-custom {
            animation: pulse-red 2s infinite;
        }
        @keyframes pulse-red {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(231, 74, 59, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(231, 74, 59, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(231, 74, 59, 0); }
        }
    </style>
</head>

<body id="page-top">

    @php
        // Đếm số lượng đánh giá đang chờ duyệt
        $pendingReviewsCount = \App\Models\Review::where('status', 'pending')->count();
        
        // Đếm số lượng đơn hàng đang yêu cầu trả hàng (Khiếu nại)
        $returningOrdersCount = \App\Models\Order::where('status', 'returning')->count();
        
        // Đếm số lượng đơn hàng cần Admin bấm nút xác nhận hoàn tiền (Trạng thái refunding)
        $refundingOrdersCount = \App\Models\Order::where('status', 'refunding')->count();

        // Tổng thông báo trên quả chuông
        $totalSystemAlerts = $pendingReviewsCount + $returningOrdersCount + $refundingOrdersCount;
    @endphp

    <div id="wrapper">
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('admin.dashboard') }}">
                <div class="sidebar-brand-icon rotate-n-15">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <div class="sidebar-brand-text mx-3">Admin Panel</div>
            </a>

            <hr class="sidebar-divider my-0">

            <li class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.dashboard') }}">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Bảng điều khiển</span></a>
            </li>

            <hr class="sidebar-divider">
            <div class="sidebar-heading">Cửa hàng</div>

            <li class="nav-item {{ request()->routeIs('admin.product.*') ? 'active' : '' }}">
                <a class="nav-link {{ request()->routeIs('admin.product.*') ? '' : 'collapsed' }}" href="#" data-toggle="collapse" data-target="#collapseProducts">
                    <i class="fas fa-fw fa-box"></i>
                    <span>Quản lý Sản phẩm</span>
                </a>
                <div id="collapseProducts" class="collapse {{ request()->routeIs('admin.product.*') ? 'show' : '' }}" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded shadow-sm">
                        <a class="collapse-item {{ request()->routeIs('admin.product.index') ? 'active' : '' }}" href="{{ route('admin.product.index') }}">Danh sách sản phẩm</a>
                    </div>
                </div>
            </li>

            <li class="nav-item {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                <a class="nav-link {{ request()->routeIs('admin.orders.*') ? '' : 'collapsed' }}" href="#" data-toggle="collapse" data-target="#collapseOrders">
                    <i class="fas fa-fw fa-file-invoice-dollar"></i>
                    <span>Quản lý Đơn hàng</span>
                    @if($returningOrdersCount > 0 || $refundingOrdersCount > 0)
                        <span class="badge badge-warning badge-counter animate-pulse-custom">!</span>
                    @endif
                </a>
                <div id="collapseOrders" class="collapse {{ request()->routeIs('admin.orders.*') ? 'show' : '' }}" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded shadow-sm">
                        <a class="collapse-item {{ !request()->has('status') && request()->routeIs('admin.orders.index') ? 'active' : '' }}" href="{{ route('admin.orders.index') }}">Tất cả đơn hàng</a>
                        
                        <div class="dropdown-divider"></div>
                        <a class="collapse-item font-weight-bold text-primary {{ request()->routeIs('admin.orders.refunds') ? 'active' : '' }}" href="{{ route('admin.orders.refunds') }}">
                            <i class="fas fa-hand-holding-usd mr-1"></i> Chờ hoàn tiền
                            @if($refundingOrdersCount > 0)
                                <span class="badge badge-primary ml-1">{{ $refundingOrdersCount }}</span>
                            @endif
                        </a>

                        <div class="dropdown-divider"></div>
                        <h6 class="collapse-header">Lọc trạng thái:</h6>
                        <a class="collapse-item {{ request('status') == 'pending' ? 'active' : '' }}" href="{{ route('admin.orders.index', ['status' => 'pending']) }}">Chờ xác nhận</a>
                        <a class="collapse-item {{ request('status') == 'shipping' ? 'active' : '' }}" href="{{ route('admin.orders.index', ['status' => 'shipping']) }}">Đang giao</a>
                        <a class="collapse-item {{ request('status') == 'success' ? 'active' : '' }}" href="{{ route('admin.orders.index', ['status' => 'success']) }}">Thành công</a>
                        
                        <div class="dropdown-divider"></div>
                        <a class="collapse-item text-danger font-weight-bold {{ request('status') == 'returning' ? 'active' : '' }}" href="{{ route('admin.orders.index', ['status' => 'returning']) }}">
                            <i class="fas fa-exclamation-triangle mr-1"></i> Đơn khiếu nại
                            @if($returningOrdersCount > 0)
                                <span class="badge badge-danger ml-1">{{ $returningOrdersCount }}</span>
                            @endif
                        </a>
                    </div>
                </div>
            </li>

            <li class="nav-item {{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.reviews.index') }}">
                    <i class="fas fa-fw fa-star"></i>
                    <span>Quản lý Đánh giá</span>
                    @if($pendingReviewsCount > 0)
                        <span class="badge badge-danger badge-counter">{{ $pendingReviewsCount > 9 ? '9+' : $pendingReviewsCount }}</span>
                    @endif
                </a>
            </li>

            <hr class="sidebar-divider">
            <div class="sidebar-heading">Hệ thống</div>

            @if(auth()->check() && auth()->user()->role === 'admin')
            <li class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.users.index') }}">
                    <i class="fas fa-fw fa-user-shield"></i>
                    <span>Quản lý thành viên</span>
                </a>
            </li>
            @endif

            <div class="text-center d-none d-md-inline mt-4">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>
        </ul>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">

                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow-sm">
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <ul class="navbar-nav ml-auto align-items-center">
                        <li class="nav-item dropdown no-arrow mx-1">
                            <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button" data-toggle="dropdown">
                                <i class="fas fa-bell fa-fw"></i>
                                @if($totalSystemAlerts > 0)
                                    <span class="badge badge-danger badge-counter">{{ $totalSystemAlerts }}</span>
                                @endif
                            </a>
                        </li>

                        <div class="topbar-divider d-none d-sm-block"></div>

                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" data-toggle="dropdown">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small font-weight-bold">{{ auth()->user()->name ?? 'Administrator' }}</span>
                                <img class="img-profile rounded-circle" 
                                     src="{{ (auth()->user() && auth()->user()->image) ? asset('uploads/users/'.auth()->user()->image) : asset('backend/img/no-image.png') }}"
                                     width="32" height="32">
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in">
                                <a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>Hồ sơ</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>Đăng xuất
                                </a>
                            </div>
                        </li>
                    </ul>
                </nav>
                <div class="container-fluid py-2">
                    @yield('content')
                </div>

            </div>

            <footer class="sticky-footer bg-white border-top">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto font-italic text-muted">
                        <span>Bản quyền &copy; {{ date('Y') }} - Hệ thống Quản lý Bán hàng</span>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog">
        <div class="modal-dialog shadow-lg" role="document">
            <div class="modal-content border-0">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title font-weight-bold">Xác nhận đăng xuất?</h5>
                    <button class="close text-white" type="button" data-dismiss="modal"><span>×</span></button>
                </div>
                <div class="modal-body">Dữ liệu phiên làm việc hiện tại sẽ kết thúc. Bạn có chắc chắn muốn thoát không?</div>
                <div class="modal-footer bg-light">
                    <button class="btn btn-secondary btn-sm" type="button" data-dismiss="modal">Hủy</button>
                    <form action="{{ route('admin.logout') }}" method="POST">
                        @csrf
                        <button class="btn btn-danger btn-sm px-4 shadow-sm" type="submit">Đăng xuất</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('backend/vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('backend/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('backend/vendor/jquery-easing/jquery.easing.min.js') }}"></script>
    <script src="{{ asset('backend/js/sb-admin-2.min.js') }}"></script>

    <div class="position-fixed p-3" style="z-index: 9999; right: 20px; top: 20px;">
        @if(session('success') || session('message'))
        <div class="toast show shadow-lg border-left-success" role="alert">
            <div class="toast-header bg-success text-white">
                <i class="fas fa-check-circle mr-2"></i>
                <strong class="mr-auto">Thành công</strong>
                <button type="button" class="ml-2 mb-1 close text-white" data-dismiss="toast">&times;</button>
            </div>
            <div class="toast-body bg-white text-dark font-weight-bold p-3">
                {{ session('success') ?? session('message') }}
            </div>
        </div>
        @endif

        @if(session('error'))
        <div class="toast show shadow-lg border-left-danger" role="alert">
            <div class="toast-header bg-danger text-white">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <strong class="mr-auto">Thông báo lỗi</strong>
                <button type="button" class="ml-2 mb-1 close text-white" data-dismiss="toast">&times;</button>
            </div>
            <div class="toast-body bg-white text-dark font-weight-bold p-3">
                {{ session('error') }}
            </div>
        </div>
        @endif
    </div>

    <script>
        $(document).ready(function(){
            // Tự động ẩn Toast sau 4 giây
            setTimeout(function() {
                $('.toast').fadeOut('slow');
            }, 4000);
        });
    </script>
    
    @stack('scripts')
</body>
</html>