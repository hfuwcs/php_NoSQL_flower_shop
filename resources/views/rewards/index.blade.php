<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-2xl font-bold text-gray-800">Reward Shop</h1>
                        <div class="text-right">
                            <span class="text-sm text-gray-500">Your Points</span>
                            <p class="text-2xl font-bold text-indigo-600">{{ number_format($userPoints) }}</p>
                        </div>
                    </div>

                    @if(session('success'))
                    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                    @endif
                    @if(session('error'))
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline">{{ session('error') }}</span>
                    </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @forelse ($rewards as $reward)
                        @php
                        $canAfford = $userPoints >= $reward->point_cost;
                        @endphp
                        <div class="border rounded-lg p-4 flex flex-col justify-between {{ !$canAfford ? 'bg-gray-50 opacity-60' : '' }}">
                            <div>
                                <h2 class="text-lg font-bold text-gray-900">{{ $reward->name }}</h2>
                                <p class="text-sm text-gray-600 mt-1">{{ $reward->description }}</p>
                            </div>
                            <div class="mt-4 flex justify-between items-center">
                                <p class="text-lg font-bold text-indigo-600">{{ number_format($reward->point_cost) }} Points</p>

                                <form action="{{ route('rewards.redeem', $reward) }}" method="POST">
                                    @csrf
                                    <button type-="submit"
                                        class="px-4 py-2 text-sm font-medium text-white rounded-md transition-colors {{ $canAfford ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-gray-400 cursor-not-allowed' }}"
                                        {{ !$canAfford ? 'disabled' : '' }}>
                                        Redeem
                                    </button>
                                </form>
                            </div>
                        </div>
                        @empty
                        <p class="md:col-span-2 lg:col-span-3 text-center text-gray-600">There are currently no rewards available. Check back later!</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>