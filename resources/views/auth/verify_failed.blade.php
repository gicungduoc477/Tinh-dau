@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Xác thực không thành công</h3>
    <p>Token không hợp lệ hoặc đã hết hạn. Vui lòng liên hệ hỗ trợ hoặc thử đăng ký lại.</p>
    <p><a href="{{ route('register') }}" class="btn btn-secondary">Đăng ký lại</a></p>
</div>
@endsection