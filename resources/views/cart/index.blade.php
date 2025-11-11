<x-app-layout>
    <div class="bg-white p-8 rounded-lg shadow-lg">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Your Shopping Cart</h1>

        @if(session('success'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
        @endif

        {{-- Sử dụng $cart['items'] thay vì $cartItems --}}
        @if(count($cart['items']) > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b">
                        <th class="py-2 px-4">Product</th>
                        <th class="py-2 px-4">Price</th>
                        <th class="py-2 px-4">Quantity</th>
                        <th class="py-2 px-4 text-right">Subtotal</th>
                        <th class="py-2 px-4 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cart['items'] as $item)
                    <tr class="border-b">
                        <td class="py-4 px-4 font-semibold">{{ $item['product_name'] }}</td>
                        <td class="py-4 px-4">${{ number_format($item['price'], 2) }}</td>
                        <td class="py-4 px-4">
                            {{-- FORM CẬP NHẬT SỐ LƯỢNG --}}
                            <form action="{{ route('cart.update', $item['product_id']) }}" method="POST" class="flex items-center">
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
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-8 flex flex-col md:flex-row justify-between items-start">
            <div class="w-full md:w-1/3">
                <h2 class="text-lg font-semibold mb-2">Apply Coupon</h2>
                <form id="coupon-form" class="flex">
                    <input type="text" id="coupon-code" name="coupon_code" placeholder="Enter coupon code" class="flex-grow rounded-l-md border-gray-300">
                    <button type="submit" class="bg-gray-800 text-white font-bold py-2 px-4 rounded-r-md hover:bg-gray-700 transition-colors">Apply</button>
                </form>
                <div id="coupon-status-message" class="mt-2 text-sm"></div>
            </div>

            <div class="w-full md:w-1/3 mt-6 md:mt-0 text-right">
                <div class="space-y-2">
                    <p class="text-lg">Subtotal: <span id="cart-subtotal" class="font-semibold">${{ number_format($cart['subtotal'], 2) }}</span></p>

                    <div id="discount-section" class="{{ $cart['applied_coupon'] ? '' : 'hidden' }}">
                        <p class="text-lg text-green-600">
                            Discount (<span id="coupon-code-display">{{ $cart['applied_coupon']['code'] ?? '' }}</span>):
                            <span id="cart-discount" class="font-semibold">-${{ number_format($cart['discount_amount'], 2) }}</span>
                        </p>
                    </div>

                    <p class="text-2xl font-bold border-t pt-2 mt-2">
                        Total: <span id="cart-final-total" class="text-primary-dark">${{ number_format($cart['final_total'], 2) }}</span>
                    </p>
                </div>
                <a href="{{ route('checkout.index') }}" class="mt-4 inline-block bg-green-500 text-white font-bold py-3 px-8 rounded-md hover:bg-green-600 transition-colors">
                    Proceed to Checkout
                </a>
            </div>
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

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const couponForm = document.getElementById('coupon-form');
            const couponCodeInput = document.getElementById('coupon-code');
            const statusMessage = document.getElementById('coupon-status-message');

            couponForm.addEventListener('submit', async function(event) {
                event.preventDefault();

                const couponCode = couponCodeInput.value;
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                statusMessage.innerHTML = '';
                statusMessage.classList.remove('text-red-500', 'text-green-500');

                try {
                    const response = await fetch('{{ route("cart.applyCoupon") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            coupon_code: couponCode
                        })
                    });

                    const data = await response.json();

                    if (response.ok) {
                        statusMessage.textContent = data.message;
                        statusMessage.classList.add('text-green-500');
                        updateCartTotals(data.cart);
                    } else {
                        statusMessage.textContent = data.message;
                        statusMessage.classList.add('text-red-500');
                    }

                } catch (error) {
                    statusMessage.textContent = 'An unexpected error occurred. Please try again.';
                    statusMessage.classList.add('text-red-500');
                    console.error('Error applying coupon:', error);
                }
            });

            function updateCartTotals(cart) {
                document.getElementById('cart-subtotal').innerText = '$' + parseFloat(cart.subtotal).toFixed(2);
                document.getElementById('cart-final-total').innerText = '$' + parseFloat(cart.final_total).toFixed(2);

                const discountSection = document.getElementById('discount-section');
                if (cart.applied_coupon) {
                    document.getElementById('coupon-code-display').innerText = cart.applied_coupon.code;
                    document.getElementById('cart-discount').innerText = '-$' + parseFloat(cart.discount_amount).toFixed(2);
                    discountSection.classList.remove('hidden');
                } else {
                    discountSection.classList.add('hidden');
                }
            }
        });
    </script>
    @endpush
</x-app-layout>