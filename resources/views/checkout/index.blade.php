<x-app-layout>
    <div class="container mx-auto p-4 lg:p-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Checkout</h1>

        <form action="{{ route('checkout.process') }}" method="POST" id="payment-form">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

                <!-- Cột trái: Thông tin giao hàng -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <h2 class="text-xl font-semibold mb-4">Shipping Information</h2>

                    {{-- Name --}}
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                        <input type="text" id="name" name="name" value="{{ auth()->user()->name }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>

                    {{-- Address --}}
                    <div class="mb-4">
                        <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                        <input type="text" id="address" name="address" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>

                    {{-- City --}}
                    <div class="mb-4">
                        <label for="city" class="block text-sm font-medium text-gray-700">City</label>
                        <input type="text" id="city" name="city" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>

                    {{-- Phone --}}
                    <div class="mb-4">
                        <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                        <input type="tel" id="phone" name="phone" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                </div>

                <!-- Cột phải: Tóm tắt đơn hàng và thanh toán -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <h2 class="text-xl font-semibold mb-4">Your Order</h2>

                    <div class="space-y-4 border-b pb-4">
                        @foreach($order->items as $item)
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="font-semibold">{{ $item->product_name }}</p>
                                <p class="text-sm text-gray-500">Qty: {{ $item->quantity }}</p>
                            </div>
                            <p class="font-semibold">${{ number_format($item->price_at_purchase * $item->quantity, 2) }}</p>
                        </div>
                        @endforeach
                    </div>


                    <div class="mt-4 flex justify-between text-xl font-bold">
                        <p>Total</p>
                        <p>${{ number_format($order->total_amount, 2) }}</p>
                    </div>

                    <div class="mt-6">
                        <h3 class="text-lg font-semibold mb-2">Payment Details</h3>
                        {{-- Stripe Payment Element sẽ được chèn vào đây --}}
                        <div id="payment-element" class="p-2 border rounded"></div>
                        <div id="payment-message" class="hidden text-red-500 text-sm mt-2"></div>
                    </div>

                    <button type="submit" id="submit-button" class="mt-6 w-full bg-green-500 text-white font-bold py-3 px-8 rounded-md hover:bg-green-600 transition-colors">
                        Place Order & Pay
                    </button>
                </div>

            </div>
        </form>
    </div>

    {{-- Script --}}
    @pushOnce('scripts')
        <script src="https://js.stripe.com/v3/"></script>
    @endPushOnce
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const stripe = Stripe("{{ $stripeKey }}");
            const elements = stripe.elements({
                clientSecret: "{{ $clientSecret }}"
            });
            const paymentElement = elements.create('payment');
            paymentElement.mount('#payment-element');

            const form = document.getElementById('payment-form');
            const submitButton = document.getElementById('submit-button');
            const messageContainer = document.getElementById('payment-message');

            form.addEventListener('submit', async function(event) {
                event.preventDefault();
                submitButton.disabled = true;
                submitButton.textContent = 'Processing...';
                messageContainer.classList.add('hidden');

                const formData = new FormData(form);
                const addressData = {
                    name: formData.get('name'),
                    address: formData.get('address'),
                    city: formData.get('city'),
                    phone: formData.get('phone'),
                    _token: formData.get('_token')
                };

                try {
                    const response = await fetch("{{ route('checkout.process') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(addressData)
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.message || 'Failed to save address.');
                    }

                    const {
                        error
                    } = await stripe.confirmPayment({
                        elements,
                        confirmParams: {
                            return_url: "{{ route('checkout.success') }}",
                        },
                    });

                    if (error) {
                        throw new Error(error.message);
                    }

                } catch (error) {
                    messageContainer.textContent = error.message;
                    messageContainer.classList.remove('hidden');
                    submitButton.disabled = false;
                    submitButton.textContent = 'Place Order & Pay';
                }
            });
        });
    </script>
    @endpush
</x-app-layout>