{{-- Blade snippet: show admin menu only to admins --}}
@if(auth()->check() && auth()->user()->isAdmin())
    <ul class="nav flex-column">
        <li class="nav-item"><a class="nav-link" href="{{ route('admin.dashboard') }}">Admin Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="{{ route('admin.product.index') }}">Products</a></li>
        <li class="nav-item"><a class="nav-link" href="{{ route('admin.orders.index') }}">Orders</a></li>
    </ul>
@endif