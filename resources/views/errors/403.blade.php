<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>403 - {{ __('Truy cập bị từ chối') }} - {{ config('app.name', 'Laravel') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-100">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 text-center">
            <div>
                <svg class="mx-auto h-24 w-24 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            
            <div>
                <h1 class="text-6xl font-extrabold text-gray-900">403</h1>
                <h2 class="mt-2 text-2xl font-bold text-gray-900">{{ __('Truy cập bị từ chối') }}</h2>
                <p class="mt-4 text-gray-600">
                    {{ __('Bạn không có quyền truy cập trang này.') }}
                </p>
            </div>

            <div class="mt-8 space-y-4">
                <a href="{{ url('/') }}" 
                   class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
                    <svg class="mr-2 -ml-1 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    {{ __('Về trang chủ') }}
                </a>
                
                @guest
                <div>
                    <a href="{{ route('login') }}" 
                       class="text-indigo-600 hover:text-indigo-500 font-medium">
                        {{ __('Đăng nhập với tài khoản khác') }}
                    </a>
                </div>
                @else
                <div class="text-sm text-gray-500">
                    {{ __('Đang đăng nhập với:') }} <strong>{{ auth()->user()->email }}</strong>
                </div>
                @endguest
            </div>
        </div>
    </div>
</body>
</html>
