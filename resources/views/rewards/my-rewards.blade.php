<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h1 class="text-2xl font-bold text-gray-800 mb-6">My Rewards</h1>

                    <div class="space-y-4">
                        @forelse ($userRewards as $userReward)
                            <div class="border rounded-lg p-4 flex flex-col sm:flex-row justify-between items-start sm:items-center">
                                <div class="mb-4 sm:mb-0">
                                    {{-- Kiểm tra xem reward relationship có được tải không --}}
                                    @if ($userReward->reward)
                                        <h2 class="text-lg font-bold text-gray-900">{{ $userReward->reward->name }}</h2>
                                        <p class="text-sm text-gray-600 mt-1">{{ $userReward->reward->description }}</p>
                                    @else
                                        <h2 class="text-lg font-bold text-gray-900">Reward Information Unavailable</h2>
                                    @endif
                                    <p class="text-xs text-gray-500 mt-2">Claimed on: {{ $userReward->claimed_at?->format('d/m/Y') ?? 'N/A' }}</p>
                                </div>
                                
                                <div class="bg-indigo-50 p-3 rounded-lg text-center">
                                    <p class="text-sm text-indigo-800 font-medium">Your Coupon Code:</p>
                                    <p class="text-lg font-bold text-indigo-900 tracking-wider mt-1">
                                        {{ $userReward->reward_data['coupon_code'] ?? 'N/A' }}
                                    </p>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-12">
                                <p class="text-xl text-gray-600">You haven't redeemed any rewards yet.</p>
                                <a href="{{ route('rewards.index') }}" class="mt-4 inline-block bg-indigo-600 text-white font-bold py-2 px-6 rounded-md hover:bg-indigo-700 transition-colors">
                                    Visit the Reward Shop
                                </a>
                            </div>
                        @endforelse
                    </div>

                    <div class="mt-8">
                        {{ $userRewards->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>