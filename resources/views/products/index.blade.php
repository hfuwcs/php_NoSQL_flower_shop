<x-app-layout>
    <div class="container mx-auto p-4">
        <h1 class="text-4xl font-bold text-primary-dark mb-8 text-center">Our Products</h1>

        <!-- FORM LỌC SẢN PHẨM -->
        <div class="bg-white p-4 rounded-lg shadow mb-8">
            <form action="{{ route('products.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                {{-- Lọc theo Category --}}
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
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

                {{-- Nút Submit và Reset --}}
                <div class="flex space-x-2">
                    <button type="submit" class="w-full bg-primary-dark text-white px-4 py-2 rounded-md hover:bg-pink-500">Filter</button>
                    <a href="{{ route('products.index') }}" class="w-full text-center bg-gray-300 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-400">Reset</a>
                </div>
            </form>
        </div>

        {{-- DANH SÁCH SẢN PHẨM --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach ($products as $product)
            <a href="{{ route('products.show', $product) }}" class="block bg-white p-4 rounded-lg shadow hover:shadow-xl transition-shadow duration-300">
                <img src="{{ $product->images[0] ?? 'https://via.placeholder.com/400' }}" alt="{{ $product->name }}" class="w-full h-48 object-cover rounded-md mb-4">
                <h2 class="text-lg font-semibold text-gray-800">{{ $product->name }}</h2>
                <p class="text-text-muted text-sm">{{ $product->category }}</p>
            </a>
            @endforeach
        </div>

        {{-- PHÂN TRANG --}}
        <div class="mt-8">
            {{ $products->appends(request()->query())->links() }}
        </div>
    </div>
</x-app-layout>