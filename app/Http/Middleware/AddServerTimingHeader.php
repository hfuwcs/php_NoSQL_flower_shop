<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AddServerTimingHeader
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Đo PHP bootstrap time (từ request start đến middleware này)
        $bootstrapTime = 0;
        if (defined('LARAVEL_START')) {
            $bootstrapTime = (microtime(true) - LARAVEL_START) * 1000;
        }
        
        // Bắt đầu đo thời gian xử lý application
        $startTime = microtime(true);
        
        // Xử lý request
        $response = $next($request);
        
        // Tính thời gian xử lý (ms)
        $appDuration = (microtime(true) - $startTime) * 1000;
        $totalDuration = defined('LARAVEL_START') 
            ? (microtime(true) - LARAVEL_START) * 1000 
            : $appDuration;
        
        // Thêm Server-Timing header chi tiết
        $timings = [
            sprintf('total;desc="Total";dur=%.2f', $totalDuration),
            sprintf('bootstrap;desc="Laravel Bootstrap";dur=%.2f', $bootstrapTime),
            sprintf('app;desc="Application";dur=%.2f', $appDuration),
        ];
        
        $response->headers->set('Server-Timing', implode(', ', $timings));
        
        // Thêm custom headers để dễ debug
        $response->headers->set('X-Response-Time', sprintf('%.2fms', $totalDuration));
        $response->headers->set('X-Bootstrap-Time', sprintf('%.2fms', $bootstrapTime));
        $response->headers->set('X-App-Time', sprintf('%.2fms', $appDuration));
        
        return $response;
    }
}
