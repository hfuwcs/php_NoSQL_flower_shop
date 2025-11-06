<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Csp\AddCspHeaders;
use App\Http\Middleware\AddServerTimingHeader;
use App\Http\Middleware\SetLocale;

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
        //
    })->create();
