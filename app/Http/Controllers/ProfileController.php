<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Hiển thị trang hồ sơ người dùng
     */
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();
        return view('profile.index', compact('user'));
    }

    /**
     * Hàm edit để fix lỗi "Method edit does not exist"
     * Thông thường trang edit và index có thể dùng chung 1 view nếu bạn làm form tại chỗ
     */
    public function edit()
    {
        /** @var User $user */
        $user = Auth::user();
        
        // Nếu bạn có file edit.blade.php riêng thì sửa thành 'profile.edit'
        // Còn nếu dùng chung thì để 'profile.index'
        return view('profile.index', compact('user'));
    }

    /**
     * Cập nhật thông tin cá nhân
     */
    public function update(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20', // Thêm phone nếu cần
            'address' => 'nullable|string|max:500', // Thêm address nếu cần
        ]);

        $user->update($data);

        return back()->with('success', 'Hồ sơ đã được cập nhật thành công.');
    }

    /**
     * Cập nhật mật khẩu
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|confirmed|min:8',
        ], [
            'password.confirmed' => 'Mật khẩu xác nhận không khớp.',
            'password.min' => 'Mật khẩu phải ít nhất 8 ký tự.'
        ]);

        /** @var User $user */
        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Mật khẩu hiện tại không chính xác.']);
        }

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return back()->with('success', 'Đổi mật khẩu thành công.');
    }
}