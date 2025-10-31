<x-app-layout>
    <div class="container mx-auto p-8 text-center">
        <h1 class="text-3xl font-bold text-green-500">Payment Successful!</h1>
        <p class="mt-4 text-lg">Thank you for your order. We are processing it and will notify you upon shipment.</p>
        <a href="{{ route('products.index') }}" class="mt-6 inline-block bg-primary-dark text-white font-bold py-2 px-6 rounded-md">Continue Shopping</a>
    </div>
</x-app-layout>