<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h1 class="text-2xl font-bold text-gray-800 mb-6">My Order History</h1>

                    @if(session('success'))
                    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                    @endif

                    <div class="space-y-8">
                        @forelse ($orders as $order)
                        <div class="border rounded-lg p-4">
                            <div class="flex justify-between items-center border-b pb-2 mb-2">
                                <div>
                                    <h2 class="font-bold">Order #{{ $order->id }}</h2>
                                    <p class="text-sm text-gray-500">Placed on: {{ $order->created_at->format('d/m/Y') }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-lg">${{ number_format($order->total_amount, 2) }}</p>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        {{ Str::ucfirst($order->status) }}
                                    </span>
                                </div>
                            </div>

                            <h3 class="font-semibold mt-4 mb-2">Items in this order:</h3>
                            <ul class="space-y-3">
                                @foreach ($order->items as $item)
                                <li class="flex justify-between items-center p-2 bg-gray-50 rounded-md">
                                    <div>
                                        <p class="font-medium">{{ $item->product_name }}</p>
                                        <p class="text-sm text-gray-600">Qty: {{ $item->quantity }} - Price: ${{ number_format($item->price_at_purchase, 2) }}</p>
                                    </div>
                                    <div>
                                        @if ($item->delivery_status === 'shipped')
                                        <form action="{{ route('order-item.confirm-delivery', $item) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="px-3 py-1 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700">
                                                Confirm Delivery
                                            </button>
                                        </form>
                                        @elseif ($item->delivery_status === 'delivered')
                                        @if (is_null($item->review_id))
                                        @if (now()->lte($item->review_deadline_at))
                                        {{-- Đủ điều kiện: Đã nhận, chưa review, VÀ CÒN HẠN --}}
                                        <a href="{{ route('reviews.create', ['orderItem' => $item->id]) }}" class="px-3 py-1 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
                                            Write a Review
                                        </a>
                                        @else
                                        {{-- Đã nhận, chưa review, NHƯNG HẾT HẠN --}}
                                        <span class="px-3 py-1 text-sm text-gray-700 bg-gray-200 rounded-md cursor-not-allowed" title="The review period for this item has expired.">
                                            Review Period Ended
                                        </span>
                                        @endif
                                        @else
                                        {{-- Đã nhận và đã có review rồi --}}
                                        <span class="px-3 py-1 text-sm font-medium text-green-800 bg-green-100 rounded-md">
                                            Reviewed
                                        </span>
                                        @endif
                                    </div>
                                </li>
                                @endif
                                @endforeach
                            </ul>
                        </div>
                        @empty
                        <p class="text-gray-600">You have not placed any orders yet.</p>
                        @endforelse
                    </div>

                    <div class="mt-8">
                        {{ $orders->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>