<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    // Hiển thị danh sách người dùng
    public function index(Request $request)
    {
        $users = User::select('id', 'name', 'email', 'role', 'created_at')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    // Hiển thị form thêm mới
    public function create()
    {
        return view('admin.users.create');
    }

    // Lưu người dùng mới vào database
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:user,admin'],
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Thêm người dùng thành công!');
    }

    // Hiển thị form chỉnh sửa
    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('admin.users.edit', compact('user'));
    }

    // Cập nhật thông tin người dùng
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$id],
            'role' => ['required', 'in:user,admin'],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'Cập nhật người dùng thành công!');
    }

    // Xóa người dùng (Đã sửa lỗi biến $user)
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // Bảo mật: So sánh ID admin đang đăng nhập với ID user muốn xóa
        if (auth()->id() == $user->id) {
            return redirect()->route('admin.users.index')->with('error', 'Bạn không thể tự xóa chính mình!');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Đã xóa người dùng thành công!');
    }
}