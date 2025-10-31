<x-app-layout>
    <div class="container mx-auto p-4">
        @if($query)
            <h1 class="text-3xl font-bold text-gray-800 mb-6">Search results for: "<span class="text-primary-dark">{{ $query }}</span>"</h1>
        @else
            <h1 class="text-3xl font-bold text-gray-800 mb-6">Search Products</h1>
        @endif

        @if($query && $products->isEmpty())
            <p class="text-center text-text-muted py-10">No products found matching your search.</p>
        @elseif(!$query)
            <p class="text-center text-text-muted py-10">Please enter a search term to find products.</p>
        @else
            {{-- Tái sử dụng giao diện hiển thị danh sách sản phẩm --}}
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
                {{-- Thêm query vào link phân trang để không bị mất khi chuyển trang --}}
                {{ $products->appends(['query' => $query])->links() }}
            </div>
        @endif
    </div>
</x-app-layout>