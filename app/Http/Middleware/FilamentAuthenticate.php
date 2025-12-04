<?php

namespace App\Http\Middleware;

use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * Custom Authenticate middleware cho Filament admin panel.
 * 
 * Thay vì abort 403 khi user không có quyền admin,
 * redirect về trang chính với thông báo lỗi thân thiện.
 */
class FilamentAuthenticate extends Middleware
{
    /**
     * @param  array<string>  $guards
     */
    protected function authenticate($request, array $guards): void
    {
        $guard = Filament::auth();

        // Nếu chưa đăng nhập, redirect đến trang login của Filament
        if (! $guard->check()) {
            $this->unauthenticated($request, $guards);
            return;
        }

        $this->auth->shouldUse(Filament::getAuthGuard());

        /** @var Model $user */
        $user = $guard->user();

        $panel = Filament::getCurrentOrDefaultPanel();

        // Kiểm tra quyền truy cập panel
        $canAccess = $user instanceof FilamentUser 
            ? $user->canAccessPanel($panel) 
            : (config('app.env') === 'local');

        if (! $canAccess) {
            // Thay vì abort 403, redirect về trang chính với thông báo
            $this->handleUnauthorizedAccess($request);
        }
    }

    /**
     * Xử lý khi user không có quyền truy cập admin panel.
     * Redirect về trang chính với thông báo lỗi.
     */
    protected function handleUnauthorizedAccess(Request $request): never
    {
        // Redirect về trang chính với thông báo lỗi
        $response = redirect()
            ->to('/')
            ->with('error', __('Bạn không có quyền truy cập trang quản trị. Vui lòng đăng nhập bằng tài khoản admin.'));
        
        // Throw exception để dừng request và redirect
        throw new \Illuminate\Http\Exceptions\HttpResponseException($response);
    }

    protected function redirectTo($request): ?string
    {
        return Filament::getLoginUrl();
    }
}
