<x-app-layout>
<div class="bg-white p-8 rounded-lg shadow-lg">
    <a href="{{ route('products.index') }}" class="text-primary-dark hover:text-pink-600 mb-6 block transition-colors">&larr; Back to Products</a>

    @if (session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
        <span class="block sm:inline">{{ session('success') }}</span>
    </div>
    @endif
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div>
            <img src="{{ $product->images[0] ?? 'https://via.placeholder.com/600' }}" alt="{{ $product->name }}" class="w-full h-auto object-cover rounded-lg shadow-md">
        </div>
        <div>
            <p class="text-text-muted text-sm uppercase">{{ $product->category }}</p>
            <h1 class="text-4xl font-bold text-gray-900 mt-1">{{ $product->name }}</h1>
            <p class="mt-4 text-gray-700 leading-relaxed">{{ $product->description }}</p>

            <div class="mt-8">
                <form action="{{ route('cart.add', $product) }}" method="POST">
                    @csrf
                    <div class="flex items-center space-x-4">
                        <input type="number" name="quantity" value="1" min="1" class="w-20 rounded-md border-gray-300">
                        <button type="submit" class="bg-primary-dark text-white font-bold py-2 px-6 rounded-md hover:bg-pink-500 transition-colors">
                            Add to Cart
                        </button>
                    </div>
                </form>
            </div>

            @if (session('success'))
            <div class="mt-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
            @endif

            <div class="mt-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Reviews ({{ $reviews->total() }})</h2>
                @auth
                <form action="{{ route('reviews.store', $product) }}" method="POST" class="bg-gray-50 p-6 rounded-lg mb-8 border border-gray-200">
                    @csrf
                    <h3 class="text-lg font-semibold mb-4 text-gray-800">Write your review</h3>

                    {{-- Rating Input --}}
                    <div class="mb-4">
                        <label for="rating" class="block text-sm font-medium text-gray-700">Rating</label>
                        <select name="rating" id="rating" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-dark focus:ring focus:ring-primary-dark focus:ring-opacity-50">
                            <option value="5">5 Stars</option>
                            <option value="4">4 Stars</option>
                            <option value="3">3 Stars</option>
                            <option value="2">2 Stars</option>
                            <option value="1">1 Star</option>
                        </select>
                        @error('rating') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-4">
                        <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                        <input type="text" name="title" id="title" value="{{ old('title') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-dark focus:ring focus:ring-primary-dark focus:ring-opacity-50">
                        @error('title') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-4">
                        <label for="content" class="block text-sm font-medium text-gray-700">Content</label>
                        <textarea name="content" id="content" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-dark focus:ring focus:ring-primary-dark focus:ring-opacity-50">{{ old('content') }}</textarea>
                        @error('content') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                    </div>

                    <button type="submit" class="w-full bg-primary-dark text-white px-4 py-2 rounded-md hover:bg-pink-500 transition-colors font-semibold">Submit Review</button>
                </form>
                @else
                <p class="mb-8 text-text-muted p-4 bg-gray-50 rounded-lg border">You must <a href="{{ route('login') }}" class="text-primary-dark underline font-semibold">log in</a> to write a review.</p>
                @endauth

                <div class="space-y-6">
                    @forelse ($reviews as $review)
                    <div class="border-t border-gray-200 pt-6">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center">
                                <span class="font-bold text-gray-800">{{ $review->user->name ?? 'Anonymous' }}</span>
                                <span class="text-text-muted text-sm ml-3">- {{ $review->created_at->diffForHumans() }}</span>
                            </div>
                            @if($review->upvotes > 0)
                                <span class="bg-green-100 text-green-800 text-xs font-semibold px-2.5 py-0.5 rounded">
                                    🔥 {{ $review->upvotes }} helpful
                                </span>
                            @endif
                        </div>
                        <div class="flex items-center mb-2">
                            @for ($i = 0; $i < 5; $i++)
                                <svg class="w-5 h-5 {{ $i < $review->rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.957a1 1 0 00.95.69h4.16c.969 0 1.371 1.24.588 1.81l-3.364 2.44a1 1 0 00-.364 1.118l1.287 3.957c.3.921-.755 1.688-1.54 1.118l-3.364-2.44a1 1 0 00-1.175 0l-3.364 2.44c-.784.57-1.838-.197-1.539-1.118l1.287-3.957a1 1 0 00-.364-1.118L2.07 9.384c-.783-.57-.38-1.81.588-1.81h4.16a1 1 0 00.95-.69L9.049 2.927z" />
                                </svg>
                            @endfor
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800">{{ $review->title }}</h3>
                        <p class="text-gray-600 mt-1">{{ $review->content }}</p>
                        <div class="mt-4 flex items-center space-x-4 js-vote-container" data-review-id="{{ $review->id }}">
                            <span class="text-sm text-text-muted">Was this review helpful?</span>

                            <form class="js-vote-form">
                                @csrf
                                <input type="hidden" name="vote_type" value="up">
                                <button type="submit" class="flex items-center space-x-1 text-gray-500 hover:text-green-500 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                    </svg>
                                    <span class="js-upvote-count">{{ $review->upvotes ?? 0 }}</span>
                                </button>
                            </form>

                            <form class="js-vote-form">
                                @csrf
                                <input type="hidden" name="vote_type" value="down">
                                <button type="submit" class="flex items-center space-x-1 text-gray-500 hover:text-red-500 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                    <span class="js-downvote-count">{{ $review->downvotes ?? 0 }}</span>
                                </button>
                            </form>
                        </div>
                    </div>
                    @empty
                    <div class="border-t border-gray-200 pt-6">
                        <p class="text-text-muted">No reviews yet. Be the first to write one!</p>
                    </div>
                    @endforelse

                    @if($reviews->hasPages())
                        <div class="mt-8 border-t border-gray-200 pt-6">
                            {{ $reviews->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
</x-app-layout>