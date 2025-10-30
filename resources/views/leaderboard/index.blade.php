@extends('layouts.layout')

@section('content')
    <div class="bg-white p-8 rounded-lg shadow-lg">
        <h1 class="text-4xl font-bold text-primary-dark mb-2 text-center">Top Rated Products</h1>
        <p class="text-center text-text-muted mb-8">Discover the products our customers love the most!</p>

        <div class="space-y-4">
            @forelse ($products as $index => $product)
                <a href="{{ route('products.show', $product) }}" class="flex items-center bg-gray-50 hover:bg-gray-100 p-4 rounded-lg transition-colors duration-300">
                    <div class="text-3xl font-bold text-primary-dark w-16 text-center">{{ $index + 1 }}</div>
                    <img src="{{ $product->images[0] ?? 'https://via.placeholder.com/150' }}" alt="{{ $product->name }}" class="w-16 h-16 object-cover rounded-md mx-4">
                    <div class="flex-grow">
                        <h2 class="text-lg font-semibold text-gray-800">{{ $product->name }}</h2>
                        <p class="text-text-muted text-sm">{{ $product->category }}</p>
                    </div>
                    <div class="text-right">
                        <div class="text-xl font-bold text-yellow-500">{{ number_format($product->average_rating, 2) }} ★</div>
                        <div class="text-sm text-text-muted">{{ $product->review_count }} reviews</div>
                    </div>
                </a>
            @empty
                <p class="text-center text-text-muted py-10">The leaderboard is being generated. Please check back later.</p>
            @endforelse
        </div>
    </div>
@endsection