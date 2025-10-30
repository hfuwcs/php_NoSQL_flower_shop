{# <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flower Shop Reviews</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-primary-light font-sans text-text-default">
    <nav class="bg-white shadow-sm">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <a href="{{ route('products.index') }}" class="text-xl font-bold text-primary-dark">FlowerShop</a>
            <div class="flex items-center space-x-4">
                <a href="{{ route('leaderboard.index') }}" class="text-text-muted hover:text-primary-dark">Leaderboard</a>
                @auth
                    <span>Welcome, {{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-text-muted hover:text-primary-dark">Logout</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="text-text-muted hover:text-primary-dark">Login</a>
                    <a href="{{ route('register') }}" class="text-text-muted hover:text-primary-dark">Register</a>
                @endauth
            </div>
        </div>
    </nav>
    <div class="container mx-auto p-4">
        @yield('content')
    </div>
</body>
</html> #}