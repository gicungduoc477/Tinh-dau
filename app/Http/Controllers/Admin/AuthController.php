<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
// --- ĐẢM BẢO CÁC DÒNG NÀY ĐẦY ĐỦ ĐỂ HẾT BÁO ĐỎ ---
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\WelcomeUserMail;
// -------------------------------------------

class AuthController extends Controller
{
    /**
     * Hiển thị trang đăng nhập dành riêng cho Admin
     */
    public function showLogin() {
        if (Auth::check()) {
            if (Auth::user()->role === 'admin') {
                return redirect()->route('admin.dashboard');
            }
            return redirect('/')->with('error', 'Bạn không có quyền truy cập trang quản trị.');
        }

        return view('admin.login');
    }

    /**
     * Xử lý đăng nhập dành riêng cho Admin
     */
    public function login(Request $request)
    {
        $request->validate([
            'identifier' => 'required',
            'password' => 'required',
        ], [
            'identifier.required' => 'Vui lòng nhập Email hoặc Số điện thoại.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
        ]);

        $identifier = trim($request->input('identifier'));
        $password = $request->input('password');

        $fieldType = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        $user = User::where($fieldType, $identifier)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return back()->withErrors(['identifier' => 'Thông tin đăng nhập không chính xác.'])
                         ->withInput()
                         ->with('error', 'Thông tin đăng nhập không chính xác.');
        }

        if ($user->role !== 'admin') {
            return back()->withErrors(['identifier' => 'Bạn không có quyền truy cập vào khu vực quản trị.'])
                         ->withInput()
                         ->with('error', 'Tài khoản của bạn không có quyền Quản trị viên.');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->route('admin.dashboard')->with('message', 'Chào mừng Quản trị viên!');
    }

    /**
     * Hiển thị trang đăng ký Admin
     */
    public function showRegister() {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.register');
    }

    /**
     * Xử lý đăng ký Admin
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|confirmed|min:8',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'admin', 
        ]);

        // --- LOGIC GỬI MAIL CÓ GHI LOG CHI TIẾT ---
        try {
            Mail::to($user->email)->send(new WelcomeUserMail($user));
        } catch (\Exception $e) {
            // Ghi lỗi chi tiết vào file log của Render để dễ kiểm tra
            Log::error("==========================================");
            Log::error("LỖI GỬI MAIL TẠI RENDER:");
            Log::error("Message: " . $e->getMessage());
            Log::error("Trace: " . $e->getTraceAsString());
            Log::error("==========================================");
        }
        // -------------------------------------------

        Auth::login($user);

        return redirect()->route('admin.dashboard')->with('message', 'Tạo tài khoản quản trị thành công.');
    }

    /**
     * Đăng xuất Admin
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('home')->with('message', 'Đã đăng xuất khỏi hệ thống quản trị.');
    }
}