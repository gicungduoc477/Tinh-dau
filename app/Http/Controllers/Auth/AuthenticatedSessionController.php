<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\Cart; 
use Illuminate\Support\Facades\Log;

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

        // 4. Lấy giỏ hàng từ session TRƯỚC khi regenerate (vì regenerate có thể làm mất session cũ)
        $sessionCart = session('cart', []);

        // 5. Đăng nhập thành công, làm mới session để tránh tấn công Session Fixation
        $request->session()->regenerate();

        // 6. --- LOGIC GỘP GIỎ HÀNG ---
        // Sử dụng try-catch để nếu giỏ hàng lỗi thì vẫn cho người dùng vào trang web
        try {
            $this->mergeCartAfterLogin($sessionCart);
        } catch (\Exception $e) {
            Log::error("Lỗi gộp giỏ hàng: " . $e->getMessage());
        }

        // 7. Xử lý chuyển hướng dựa trên vai trò (Role)
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
     */
    protected function mergeCartAfterLogin($sessionCart)
    {
        if (!empty($sessionCart)) {
            $userId = Auth::id();

            foreach ($sessionCart as $key => $details) {
                // XỬ LÝ FIX LỖI: Nếu key là "23_subscription", lấy ra con số 23
                $cleanProductId = $key;
                if (is_string($key) && str_contains($key, '_')) {
                    $cleanProductId = explode('_', $key)[0];
                }

                $cleanProductId = (int)$cleanProductId;

                // Nếu product_id không hợp lệ (bằng 0), bỏ qua để tránh lỗi DB
                if ($cleanProductId <= 0) continue;

                // Kiểm tra xem trong DB đã có sản phẩm đó chưa
                $cartItem = Cart::where('user_id', $userId)
                                ->where('product_id', $cleanProductId)
                                ->first();

                if ($cartItem) {
                    $cartItem->increment('quantity', $details['quantity']);
                } else {
                    Cart::create([
                        'user_id'    => $userId,
                        'product_id' => $cleanProductId,
                        'quantity'   => $details['quantity'],
                        'price'      => $details['price'] ?? 0
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