<x-app-layout>
    <div class="bg-white p-8 rounded-lg shadow-lg">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Your Shopping Cart</h1>


        @if(session('success'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
        @endif

        @if(count($cartItems) > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b">
                        <th class="py-2 px-4">Product</th>
                        <th class="py-2 px-4">Price</th>
                        <th class="py-2 px-4">Quantity</th>
                        <th class="py-2 px-4 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cartItems as $item)
                    <tr class="border-b">
                        <td class="py-4 px-4 font-semibold">{{ $item['product_name'] }}</td>
                        <td class="py-4 px-4">${{ number_format($item['price'], 2) }}</td>
                        <td class="py-4 px-4">
                            {{-- FORM CẬP NHẬT SỐ LƯỢNG --}}
                            <form action="{{ route('cart.update', $item['product_id']) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <input type="number" name="quantity" value="{{ $item['quantity'] }}" min="1" class="w-20 rounded-md border-gray-300 text-center">
                                <button type="submit" class="ml-2 text-sm text-blue-500 hover:underline">Update</button>
                            </form>
                        </td>
                        <td class="py-4 px-4 text-right">${{ number_format($item['price'] * $item['quantity'], 2) }}</td>
                        <td class="py-4 px-4 text-center">
                            {{-- FORM XÓA SẢN PHẨM --}}
                            <form action="{{ route('cart.remove', $item['product_id']) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700" onclick="return confirm('Are you sure you want to remove this item?')">Remove</button>
                            </form>
                        </td>
                        <td class="py-4 px-4 text-right">${{ number_format($item['price'] * $item['quantity'], 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6 text-right">
            <p class="text-2xl font-bold">Total: <span class="text-primary-dark">${{ number_format($cartTotal, 2) }}</span></p>
            <a href="#" class="mt-4 inline-block bg-green-500 text-white font-bold py-3 px-8 rounded-md hover:bg-green-600 transition-colors">
                Proceed to Checkout
            </a>
        </div>
        @else
        <div class="text-center py-12">
            <p class="text-xl text-text-muted">Your cart is empty.</p>
            <a href="{{ route('products.index') }}" class="mt-4 inline-block bg-primary-dark text-white font-bold py-2 px-6 rounded-md hover:bg-pink-500 transition-colors">
                Continue Shopping
            </a>
        </div>
        @endif
    </div>
</x-app-layout>