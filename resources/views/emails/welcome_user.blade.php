<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Chào mừng bạn đến với Nature Shop</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 8px; overflow: hidden; border: 1px solid #ddd; }
        .header { background-color: #27ae60; padding: 30px; text-align: center; color: white; }
        .header h1 { margin: 0; font-size: 24px; text-transform: uppercase; letter-spacing: 2px; }
        .content { padding: 30px; }
        .content h2 { color: #27ae60; margin-top: 0; }
        .user-info { background: #f9f9f9; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #27ae60; }
        .button { display: inline-block; padding: 12px 25px; background-color: #27ae60; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; margin-top: 20px; }
        .footer { background: #f4f4f4; padding: 20px; text-align: center; font-size: 12px; color: #777; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>NATURE SHOP</h1>
        </div>
        <div class="content">
            <h2>Chào mừng, {{ $user->name }}!</h2>
            <p>Cảm ơn bạn đã tin tưởng và đăng ký thành viên tại <strong>Nature Shop</strong> - Nơi cung cấp những sản phẩm thuần tự nhiên tốt nhất cho sức khỏe của bạn.</p>
            
            <div class="user-info">
                <strong>Thông tin tài khoản của bạn:</strong><br>
                Email: {{ $user->email }}<br>
                Ngày tham gia: {{ date('d/m/Y') }}
            </div>

            <p>Bây giờ bạn có thể bắt đầu mua sắm và tận hưởng những ưu đãi đặc biệt dành riêng cho thành viên mới.</p>
            
            <div style="text-align: center;">
                <a href="{{ config('app.url') }}" class="button">Khám Phá Cửa Hàng Ngay</a>
            </div>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} Nature Shop. Tất cả các quyền được bảo lưu.<br>
            Địa chỉ: 123 Đường Tự Nhiên, TP. Hồ Chí Minh</p>
        </div>
    </div>
</body>
</html>