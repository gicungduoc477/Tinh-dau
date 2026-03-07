@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Đăng ký thành công</h3>
    <p>Vui lòng kiểm tra email để xác thực tài khoản. (Kiểm tra cả hòm thư rác)</p>
    <p><a href="{{ route('login') }}" class="btn btn-primary">Đăng nhập</a></p>
</div>
@endsection