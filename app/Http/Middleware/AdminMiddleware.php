<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     * Redirect unauthenticated users to admin login; logged-in non-admins back to home.
     */
    public function handle(Request $request, Closure $next)
    {
        // Not logged in -> go to admin login
        if (!Auth::check()) {
            return redirect()->route('admin.login')->with('error', 'Bạn cần đăng nhập để truy cập trang quản trị.');
        }

        // Logged in but not admin -> redirect to home with message
        $user = Auth::user();
        if (!$user || empty($user->role) || $user->role !== 'admin') {
            return redirect('/')->with('error', 'Bạn không có quyền truy cập vào trang quản trị.');
        }

        return $next($request);
    }
}
