@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Tài khoản đã được xác thực</h3>
    <p>Bây giờ bạn có thể đăng nhập vào hệ thống.</p>
    <p><a href="{{ route('login') }}" class="btn btn-primary">Đăng nhập</a></p>
</div>
@endsection