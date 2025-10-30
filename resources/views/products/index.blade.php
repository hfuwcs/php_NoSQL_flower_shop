@extends('layouts.layout')

@section('content')
    <h1 class="text-4xl font-bold text-primary-dark mb-8 text-center">Our Products</h1>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach ($products as $product)
            <a href="{{ route('products.show', $product) }}" class="block bg-white p-4 rounded-lg shadow hover:shadow-xl transition-shadow duration-300">
                <img src="{{ $product->images[0] ?? 'https://via.placeholder.com/400' }}" alt="{{ $product->name }}" class="w-full h-48 object-cover rounded-md mb-4">
                <h2 class="text-lg font-semibold text-gray-800">{{ $product->name }}</h2>
                <p class="text-text-muted text-sm">{{ $product->category }}</p>
            </a>
        @endforeach
    </div>
    <div class="mt-8">
        {{ $products->links() }}
    </div>
@endsection