<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;

/**
 * THÊM 2 DÒNG NÀY ĐỂ HẾT BÁO ĐỎ TRONG VS CODE
 * Đảm bảo file WelcomeUserMail.php đã nằm trong app/Mail
 */
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeUserMail;

class RegisteredUserController extends Controller
{
    /**
     * Hiển thị form đăng ký
     */
    public function create()
    {
        return view('auth.register');
    }

    /**
     * Xử lý lưu thông tin đăng ký và gửi mail
     */
    public function store(Request $request)
    {
        // 1. Kiểm tra dữ liệu đầu vào
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email:rfc,dns', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $attributes = [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ];

        // Tự động gán role 'user' nếu cột role tồn tại trong database
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'role')) {
            $attributes['role'] = 'user';
        }

        // Kiểm tra an toàn bảng users trước khi chèn dữ liệu
        if (!Schema::hasTable('users')) {
            Log::error('Đăng ký thất bại: Bảng users không tồn tại trong DB.');
            return back()->withInput()->withErrors(['email' => 'Hệ thống chưa được cấu hình.']);
        }

        // 2. Sử dụng Transaction để đảm bảo an toàn dữ liệu
        DB::beginTransaction();
        try {
            $user = User::create($attributes);
            
            // 3. GỬI MAIL CHÀO MỪNG
            try {
                // Sử dụng Mail facade để gửi email chào mừng thông qua class WelcomeUserMail
                Mail::to($user->email)->send(new WelcomeUserMail($user));
            } catch (\Exception $e) {
                // Nếu lỗi mail, chỉ ghi log để Admin kiểm tra lại SMTP, khách vẫn đăng ký thành công
                Log::error('Lỗi gửi mail chào mừng tại Nature Shop: ' . $e->getMessage());
            }

            DB::commit();
        } catch (QueryException $e) {
            DB::rollBack();
            Log::error('Lỗi SQL đăng ký:', ['msg' => $e->getMessage()]);
            return back()->withInput()->withErrors(['email' => 'Lỗi cơ sở dữ liệu khi tạo tài khoản.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi đăng ký không xác định:', ['msg' => $e->getMessage()]);
            return back()->withInput()->withErrors(['email' => 'Không thể tạo tài khoản.']);
        }

        // Kích hoạt sự kiện Registered mặc định của Laravel
        event(new Registered($user));

        // 4. Chuyển hướng về trang đăng nhập kèm thông báo thành công
        return redirect()->route('login')->with('message', 'Đăng ký thành công. Một email chào mừng đã được gửi đến bạn.');
    }
}