<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        // Validation thủ công theo logic cũ của bạn nhưng tối ưu hóa hơn
        $data = $request->only(['name', 'email', 'phone', 'password', 'password_confirmation', 'agree_terms']);
        $errors = [];

        if (empty($data['name'])) $errors['name'] = 'Họ tên là bắt buộc.';
        if (empty($data['email']) && empty($data['phone'])) $errors['contact'] = 'Vui lòng nhập Email hoặc Số điện thoại.';
        if (!empty($data['email']) && User::checkEmailExists($data['email'])) $errors['email'] = 'Email này đã được sử dụng.';
        if (!empty($data['phone']) && User::checkPhoneExists($data['phone'])) $errors['phone'] = 'Số điện thoại này đã được sử dụng.';
        
        $password = $data['password'] ?? '';
        if (strlen($password) < 8) $errors['password'] = 'Mật khẩu phải ít nhất 8 ký tự.';
        if ($password !== ($data['password_confirmation'] ?? '')) $errors['password_confirmation'] = 'Xác nhận mật khẩu không khớp.';
        if (empty($data['agree_terms'])) $errors['agree_terms'] = 'Bạn phải đồng ý với điều khoản.';

        if (!empty($errors)) return back()->withErrors($errors)->withInput();

        // Tạo User
        $token = bin2hex(random_bytes(32));
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($password),
            'role' => 'user',
            'is_verified' => 0,
            'verify_token' => $token,
            'verify_token_expires_at' => now()->addDays(1),
        ]);

        return view('auth.register_success');
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        // 1. Validate dữ liệu đầu vào (identifier khớp với 'name' trong HTML)
        $request->validate([
            'identifier' => 'required',
            'password' => 'required',
        ], [
            'identifier.required' => 'Vui lòng nhập Email hoặc Số điện thoại.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
        ]);

        $identifier = trim($request->input('identifier'));
        $password = $request->input('password');

        // 2. Tìm user bằng Email hoặc Phone
        $user = User::findByEmailOrPhone($identifier);

        if (!$user || !Hash::check($password, $user->password)) {
            return back()->withErrors(['identifier' => 'Thông tin đăng nhập không chính xác.'])->withInput();
        }

        // 3. Kiểm tra xác thực tài khoản
        if (!$user->is_verified) {
            return back()->withErrors(['identifier' => 'Tài khoản chưa được xác thực. Vui lòng kiểm tra email.'])->withInput();
        }

        // 4. Đăng nhập chính thống bằng Laravel Auth
        Auth::login($user, $request->has('remember'));
        $request->session()->regenerate();

        // If AJAX / fetch request, return JSON including role and redirect path
        if ($request->expectsJson()) {
            $redirect = $user->isAdmin() ? url('/admin') : url('/');
            return response()->json([
                'success' => true,
                'role' => $user->role,
                'redirect' => $redirect,
            ], 200);
        }

        // chuyển hướng theo vai trò
        if ($user->isAdmin()) {
            return redirect('/admin');
        }

        return redirect('/products');
}

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login')->with('message', 'Bạn đã đăng xuất thành công.');
    }

    /** Show forgot password form */
    public function showForgotPassword()
    {
        return view('auth.forgot_password');
    }

    /** Handle sending reset link */
    public function sendResetLink(Request $request)
    {
        $email = trim($request->input('email', ''));
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return back()->withErrors(['email' => 'Valid email is required.'])->withInput();
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            // do not reveal that email doesn't exist
            return back()->with('message', 'If that email exists, we sent a reset link.');
        }

        $token = User::createPasswordResetToken($email);
        $resetUrl = url('/reset-password?token=' . $token);

        try {
            @mail($email, 'Reset your password', "Click to reset: $resetUrl");
        } catch (\Exception $e) {
            // ignore
        }

        return back()->with('message', 'If that email exists, we sent a reset link.');
    }

    /** Show reset password form */
    public function showResetForm(Request $request)
    {
        $token = $request->query('token');
        if (empty($token)) {
            return redirect('/')->with('error', 'Invalid token.');
        }
        return view('auth.reset_password', ['token' => $token]);
    }

    /** Handle password reset */
    public function resetPassword(Request $request)
    {
        $token = $request->input('token');
        $password = $request->input('password');
        $password_confirmation = $request->input('password_confirmation');

        if (empty($token)) return back()->withErrors(['token' => 'Invalid token.']);
        if (empty($password)) return back()->withErrors(['password' => 'Password is required.']);
        if ($password !== $password_confirmation) return back()->withErrors(['password_confirmation' => 'Password confirmation does not match.']);

        $email = User::verifyPasswordResetToken($token);
        if (empty($email)) {
            return back()->withErrors(['token' => 'Invalid or expired token.']);
        }

        $ok = User::resetPassword($email, $password);
        if (!$ok) return back()->withErrors(['general' => 'Could not reset password.']);

        return redirect('/login')->with('message', 'Password has been reset. You may login now.');
    }
}
