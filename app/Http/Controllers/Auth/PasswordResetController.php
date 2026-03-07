<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    public function create()
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ], [
            'email.exists' => 'Email này không tồn tại trong hệ thống.'
        ]);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? back()->with('message', 'Link đặt lại mật khẩu đã được gửi vào Email!')
            : back()->withErrors(['email' => __($status)]);
    }

    public function edit($token, Request $request)
    {
        // Lấy email từ ?email=... trong URL để điền vào form ẩn
        return view('auth.reset-password', [
            'token' => $token, 
            'email' => $request->query('email')
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ], [
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
            'password.min' => 'Mật khẩu phải từ 8 ký tự.'
        ]);

        // Thực hiện đặt lại mật khẩu
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                // Kiểm tra nếu tìm thấy user thì mới tiến hành lưu
                if ($user) {
                    $user->forceFill([
                        'password' => Hash::make($password)
                    ])->setRememberToken(Str::random(60));
                    
                    $user->save();

                    event(new PasswordReset($user));
                }
            }
        );

        // Nếu thành công, chuyển hướng về login
        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('message', 'Mật khẩu đã được cập nhật thành công!');
        }

        // Nếu thất bại (Token hết hạn, email sai...), quay lại với lỗi từ Laravel
        return back()->withInput($request->only('email'))
                     ->withErrors(['email' => __($status)]);
    }
}