<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

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

        // 1. Kiểm tra tồn tại và mật khẩu
        if (!$user || !Hash::check($password, $user->password)) {
            return back()->withErrors(['identifier' => 'Thông tin đăng nhập không chính xác.'])
                         ->withInput()
                         ->with('error', 'Thông tin đăng nhập không chính xác.');
        }

        // 2. CHẶN KHÁCH HÀNG: Chỉ Admin mới được vào
        if ($user->role !== 'admin') {
            return back()->withErrors(['identifier' => 'Bạn không có quyền truy cập vào khu vực quản trị.'])
                         ->withInput()
                         ->with('error', 'Tài khoản của bạn không có quyền Quản trị viên.');
        }

        // 3. Đăng nhập và tạo lại Session
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

        Auth::login($user);

        return redirect()->route('admin.dashboard')->with('message', 'Tạo tài khoản quản trị thành công.');
    }

    /**
     * Đăng xuất Admin và chuyển hướng về trang chủ Website
     * Cập nhật để sửa lỗi 419 Page Expired
     */
    public function logout(Request $request)
    {
        // 1. Thực hiện đăng xuất tài khoản
        Auth::logout();

        // 2. Hủy phiên làm việc hiện tại để đảm bảo an toàn
        $request->session()->invalidate();

        // 3. Làm mới CSRF Token để tránh lỗi 419 khi quay lại
        $request->session()->regenerateToken();
        
        // 4. Chuyển hướng về trang chủ (Route 'home') thay vì trang login admin
        return redirect()->route('home')->with('message', 'Đã đăng xuất khỏi hệ thống quản trị.');
    }
}