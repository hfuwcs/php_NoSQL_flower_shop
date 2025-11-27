<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    CartController,
    CheckoutController,
    LanguageController,
    LeaderboardController,
    OrderHistoryController,
    OrderItemController,
    ProductController,
    ProfileController,
    ReviewController,
    RewardController,
    SearchController,
    StripeWebhookController
};

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');

Route::post('/products/{product}/reviews', [ReviewController::class, 'store'])
    ->middleware(['auth', 'throttle:60,1']) // 60 request / 1 phút
    ->name('reviews.store');

Route::post('/reviews/{review}/vote', [ReviewController::class, 'vote'])
    ->middleware('auth')
    ->name('reviews.vote');

// Leaderboard
Route::get('/leaderboard', [LeaderboardController::class, 'index'])->name('leaderboard.index');

// Search
Route::get('/search', [SearchController::class, 'index'])->name('search.index');

// Stripe Webhook
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handleWebhook'])
    ->name('stripe.webhook');

//Language
Route::get('/language/{locale}', [LanguageController::class, 'switch'])->name('language.switch');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    /*
    |----------------------------------------------------------------------
    | Cart
    |----------------------------------------------------------------------
    */
    Route::prefix('cart')->name('cart.')->group(function () {
        Route::get('/', [CartController::class, 'index'])->name('index');
        Route::post('/add/{product}', [CartController::class, 'add'])->name('add');
        Route::patch('/update/{productId}', [CartController::class, 'update'])->name('update');
        Route::delete('/remove/{productId}', [CartController::class, 'remove'])->name('remove');
        Route::post('/coupon', [CartController::class, 'applyCoupon'])->name('applyCoupon');
    });

    /*
    |----------------------------------------------------------------------
    | Checkout (Thanh toán)
    |----------------------------------------------------------------------
    */
    Route::prefix('checkout')->name('checkout.')->group(function () {
        Route::get('/', [CheckoutController::class, 'index'])->name('index');
        Route::post('/', [CheckoutController::class, 'process'])->name('process');
        Route::get('/success', [CheckoutController::class, 'success'])->name('success');
    });

    /*
    |----------------------------------------------------------------------
    | Orders
    |----------------------------------------------------------------------
    */
    Route::get('/my-orders', [OrderHistoryController::class, 'index'])->name('orders.history');
    Route::post('/order-items/{orderItem}/confirm-delivery', [OrderItemController::class, 'confirmDelivery'])
        ->name('order-item.confirm-delivery');

    /*
    |----------------------------------------------------------------------
    | Reviews
    |----------------------------------------------------------------------
    */
    Route::get('/reviews/create/{orderItem}', [ReviewController::class, 'create'])->name('reviews.create');

    /*
    |----------------------------------------------------------------------
    | Reviews
    |----------------------------------------------------------------------
    */
    Route::get('/rewards', [RewardController::class, 'index'])->name('rewards.index');
    Route::post('/rewards/{reward}/redeem', [RewardController::class, 'redeem'])->name('rewards.redeem');
    Route::get('/my-rewards', [RewardController::class, 'myRewards'])->name('rewards.my');

    /*
    |----------------------------------------------------------------------
    | Profile
    |----------------------------------------------------------------------
    */
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
    });
});

/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', fn() => view('dashboard'))
    ->middleware(['auth', 'verified'])
    ->name('dashboard');


/*
|--------------------------------------------------------------------------
| Auth Scaffolding
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';
