<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware kiểm tra quyền admin cho Filament panel.
 * 
 * Xử lý các trường hợp:
 * 1. User chưa đăng nhập → để Filament xử lý login
 * 2. User đăng nhập nhưng không có quyền admin → redirect về trang chính với thông báo
 * 3. User đăng nhập và có quyền admin → cho phép truy cập
 */
class CheckAdminAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        // Nếu user đã đăng nhập
        if (Auth::check()) {
            $user = Auth::user();
            
            // Kiểm tra quyền admin
            if ($user->is_admin !== true) {
                // User không có quyền admin → redirect về trang chính
                return redirect()
                    ->route('products.index')
                    ->with('error', __('Bạn không có quyền truy cập trang quản trị.'));
            }
        }
        
        // Nếu chưa đăng nhập hoặc có quyền admin → tiếp tục
        return $next($request);
    }
}
