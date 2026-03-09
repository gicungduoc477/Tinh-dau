<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\Cart; 

class AuthenticatedSessionController extends Controller
{
    /**
     * Hiển thị trang đăng nhập cho khách hàng
     */
    public function create()
    {
        return view('auth.login');
    }

    /**
     * Xử lý logic đăng nhập chung cho cả Admin và User
     */
    public function store(Request $request)
    {
        // 1. Validate dữ liệu đầu vào
        $request->validate([
            'identifier' => ['required', 'string'],
            'password'   => ['required', 'string'],
        ]);

        // 2. Xác định xem người dùng nhập Email hay Số điện thoại
        $identifier = $request->input('identifier');
        $fieldType = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        // 3. Thử đăng nhập
        $credentials = [
            $fieldType => $identifier,
            'password' => $request->password,
        ];

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'identifier' => [__('auth.failed')],
            ]);
        }

        // 4. Đăng nhập thành công, làm mới session
        $request->session()->regenerate();

        // 5. --- LOGIC GỘP GIỎ HÀNG TỪ SESSION VÀO DATABASE ---
        $this->mergeCartAfterLogin();

        // 6. Xử lý chuyển hướng dựa trên vai trò (Role)
        $user = Auth::user();

        if ($user->role === 'admin') {
            return redirect()->intended(route('admin.dashboard'))
                             ->with('message', 'Chào mừng Quản trị viên trở lại!');
        }

        return redirect()->intended(route('home'))
                         ->with('message', 'Đăng nhập thành công!');
    }

    /**
     * Hàm phụ trợ: Gộp giỏ hàng session vào DB
     * Đã tối ưu hóa bằng pull() và bổ sung purchase_mode
     */
    protected function mergeCartAfterLogin()
    {
        // Lấy dữ liệu giỏ hàng ra và xóa luôn khỏi session trong 1 bước để tránh trùng lặp
        $sessionCart = session()->pull('cart', []);
        
        if (empty($sessionCart)) {
            return;
        }

        foreach ($sessionCart as $key => $details) {
            // XỬ LÝ FIX LỖI: Lấy Product ID sạch từ key (ví dụ: "23_subscription" -> 23)
            $cleanProductId = $key;
            if (is_string($key) && str_contains($key, '_')) {
                $cleanProductId = explode('_', $key)[0];
            }

            $cleanProductId = (int)$cleanProductId;
            if ($cleanProductId <= 0) continue;

            // Lấy purchase_mode từ session, mặc định là 'once' nếu không có
            $purchaseMode = $details['purchase_mode'] ?? 'once';

            // Tìm sản phẩm hiện có trong DB của User (khớp cả ID và chế độ mua)
            $cartItem = Cart::where('user_id', Auth::id())
                            ->where('product_id', $cleanProductId)
                            ->where('purchase_mode', $purchaseMode)
                            ->first();

            if ($cartItem) {
                // Nếu đã có, cộng dồn số lượng một cách an toàn
                $cartItem->increment('quantity', (int)($details['quantity'] ?? 1));
            } else {
                // Nếu chưa có, tạo mới
                Cart::create([
                    'user_id'       => Auth::id(),
                    'product_id'    => $cleanProductId,
                    'quantity'      => (int)($details['quantity'] ?? 1),
                    'price'         => $details['price'] ?? 0,
                    'purchase_mode' => $purchaseMode
                ]);
            }
        }

        // Ép Laravel lưu lại trạng thái session ngay lập tức
        session()->save();
    }

    /**
     * Đăng xuất
     */
    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/')->with('message', 'Đã đăng xuất thành công.');
    }
}