<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <div class="max-w-xl">
                        <h2 class="text-lg font-medium text-gray-900">
                            {{ __('Loyalty Program Information') }}
                        </h2>

                        <p class="mt-1 text-sm text-gray-600">
                            {{ __("Your current loyalty status and points history.") }}
                        </p>

                        <div class="mt-6 space-y-4">
                            {{-- Hiển thị tổng điểm và hạng --}}
                            <div class="flex justify-between items-center p-4 bg-gray-50 rounded-lg">
                                <div>
                                    <span class="text-sm font-medium text-gray-500">Total Points</span>
                                    <p class="text-2xl font-bold text-gray-900">{{ number_format($user->points_total) }}</p>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-500">Membership Tier</span>
                                    @if($user->membership && isset($user->membership['name']))
                                    <p class="text-2xl font-bold text-indigo-600">{{ $user->membership['name'] }}</p>
                                    @else
                                    <p class="text-lg font-semibold text-gray-500">No Tier</p>
                                    @endif
                                </div>
                            </div>

                            {{-- Hiển thị lịch sử giao dịch --}}
                            <div class="mt-6">
                                <h3 class="text-md font-medium text-gray-800">Points History</h3>
                                @if($pointHistory->isEmpty())
                                <p class="mt-2 text-sm text-gray-500">You have no points history yet.</p>
                                @else
                                <ul class="mt-2 border-t border-gray-200">
                                    @foreach($pointHistory as $transaction)
                                    <li class="flex justify-between items-center py-3 border-b border-gray-200">
                                        <div>
                                            <p class="text-sm font-medium text-gray-800">{{ Str::title(str_replace('_', ' ', $transaction->action_type)) }}</p>
                                            <p class="text-xs text-gray-500">{{ $transaction->created_at->format('d/m/Y H:i') }}</p>
                                        </div>
                                        <span class="text-sm font-bold {{ $transaction->points_awarded > 0 ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $transaction->points_awarded > 0 ? '+' : '' }}{{ number_format($transaction->points_awarded) }}
                                        </span>
                                    </li>
                                    @endforeach
                                </ul>
                                {{-- Link phân trang --}}
                                <div class="mt-4">
                                    {{ $pointHistory->links() }}
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>