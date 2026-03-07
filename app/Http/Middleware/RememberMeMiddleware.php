<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class RememberMeMiddleware
{
    /**
     * If a remember_me cookie exists and user is not authenticated, try to log them in.
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            return $next($request);
        }

        $cookie = $request->cookie('remember_me');
        if (!$cookie) {
            return $next($request);
        }

        // cookie format: rawtoken|user_id
        [$raw, $userId] = array_pad(explode('|', $cookie), 2, null);
        if (empty($raw) || empty($userId)) {
            return $next($request);
        }

        $user = User::find($userId);
        if (!$user) return $next($request);

        if ($user->validateRememberToken($raw)) {
            Auth::login($user);
            // refresh cookie expiration
            cookie()->queue(cookie('remember_me', $raw . '|' . $user->id, 60 * 24 * 30));
        }

        return $next($request);
    }
}
