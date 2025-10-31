<x-app-layout>
    <div class="container mx-auto p-4">
        @if($query)
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Search results for: "<span class="text-primary-dark">{{ $query }}</span>"</h1>
        @else
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Search Products</h1>
        @endif


        <!-- FORM LỌC (Tái sử dụng từ trang products.index) -->
        <div class="bg-white p-4 rounded-lg shadow mb-8">
            {{-- Form này sẽ gửi đến chính trang search.index --}}
            <form action="{{ route('search.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                {{-- Thêm một trường ẩn để giữ lại query tìm kiếm ban đầu --}}
                <input type="hidden" name="query" value="{{ $query }}">

                {{-- Lọc theo Category --}}
                <div class="md:col-span-2">
                    <label for="category" class="block text-sm font-medium text-gray-700">Filter by Category</label>
                    <select name="category" id="category" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>
                            {{ ucfirst($cat) }}
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- Lọc theo Giá Tối thiểu --}}
                <div>
                    <label for="price_min" class="block text-sm font-medium text-gray-700">Min Price</label>
                    <input type="number" name="price_min" id="price_min" value="{{ request('price_min') }}" placeholder="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>

                {{-- Lọc theo Giá Tối đa --}}
                <div>
                    <label for="price_max" class="block text-sm font-medium text-gray-700">Max Price</label>
                    <input type="number" name="price_max" id="price_max" value="{{ request('price_max') }}" placeholder="1000" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>

                {{-- Nút Submit --}}
                <div>
                    <button type="submit" class="w-full bg-primary-dark text-white px-4 py-2 rounded-md hover:bg-pink-500">Apply Filters</button>
                </div>
            </form>
        </div>

        @if($query && $products->isEmpty())
        <p class="text-center text-text-muted py-10">No products found matching your search.</p>
        @elseif(!$query)
        <p class="text-center text-text-muted py-10">Please enter a search term to find products.</p>
        @else
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