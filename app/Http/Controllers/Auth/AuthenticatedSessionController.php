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
     * Đã cập nhật để fix lỗi Data truncated column 'product_id'
     */
    protected function mergeCartAfterLogin()
    {
        $sessionCart = session('cart', []);
        
        if (!empty($sessionCart)) {
            foreach ($sessionCart as $key => $details) {
                // XỬ LÝ FIX LỖI: Nếu key là "23_subscription", lấy ra con số 23
                $cleanProductId = $key;
                if (is_string($key) && str_contains($key, '_')) {
                    $cleanProductId = explode('_', $key)[0];
                }

                // Chuyển ID về kiểu int để đảm bảo an toàn DB
                $cleanProductId = (int)$cleanProductId;

                // Kiểm tra xem trong DB của user này đã có sản phẩm đó chưa
                $cartItem = Cart::where('user_id', Auth::id())
                                ->where('product_id', $cleanProductId)
                                ->first();

                if ($cartItem) {
                    // Nếu đã có, cộng dồn số lượng
                    $cartItem->increment('quantity', $details['quantity']);
                } else {
                    // Nếu chưa có, tạo mới bản ghi trong DB
                    Cart::create([
                        'user_id'    => Auth::id(),
                        'product_id' => $cleanProductId,
                        'quantity'   => $details['quantity'],
                        'price'      => $details['price']
                    ]);
                }
            }
            // Sau khi gộp xong, xóa sạch giỏ hàng trong session
            session()->forget('cart');
        }
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