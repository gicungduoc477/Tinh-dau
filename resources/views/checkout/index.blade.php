@extends('layouts.app')

@section('title', 'Thanh toán')

@section('content')
<div class="container mt-5">
    <div class="card shadow-sm">
        <div class="card-body text-center py-5">
            <h3 class="mb-3">Trang thanh toán</h3>
            <p class="text-muted">Tính năng thanh toán chưa được cài đặt. Vui lòng liên hệ quản trị hoặc quay lại mua sắm.</p>
            <a href="{{ route('products.index') }}" class="btn btn-primary">Tiếp tục mua sắm</a>
        </div>
    </div>
</div>
@endsection
