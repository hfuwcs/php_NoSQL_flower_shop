<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Csp\AddCspHeaders;
use App\Http\Middleware\AddServerTimingHeader;
use App\Http\Middleware\SetLocale;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Thêm Server Timing vào tất cả requests
        $middleware->append(AddServerTimingHeader::class);
        
        $middleware->web(append: [
            AddCspHeaders::class,
            SetLocale::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            'stripe-webhook',
            'stripe_event_webhook',
            'stripe/*',
            '/stripe/webhook',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Xử lý lỗi 403 cho admin panel - redirect về trang chính
        $exceptions->render(function (HttpException $e, Request $request) {
            if ($e->getStatusCode() === 403) {
                // Nếu đang ở admin panel và bị 403, redirect về trang chính
                if (str_starts_with($request->path(), 'admin')) {
                    return redirect()
                        ->route('products.index')
                        ->with('error', __('Bạn không có quyền truy cập trang quản trị.'));
                }
                
                // Các trang khác hiển thị view 403
                return response()->view('errors.403', [], 403);
            }
        });
    })->create();
