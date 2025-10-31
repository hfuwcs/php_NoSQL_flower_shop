<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class CheckoutController extends Controller
{
    public function __construct(
        protected CartService $cartService,
        protected OrderService $orderService
    ) {}

    /**
     * Hiển thị trang checkout.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $order = $this->orderService->createPendingOrderFromCart($user);

        if (!$order) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $order->load('items');

        Stripe::setApiKey(config('stripe.secret'));

        $paymentIntent = PaymentIntent::create([
            'amount' => (int) ($order->total_amount * 100),
            'currency' => 'usd',
            'automatic_payment_methods' => ['enabled' => true],
            'metadata' => [
                'order_id' => $order->id,
            ],
        ]);

        return view('checkout.index', [
            'order' => $order,
            'clientSecret' => $paymentIntent->client_secret,
            'stripeKey' => config('stripe.key'),
        ]);
    }

    /**
     * Xử lý việc tạo đơn hàng và bắt đầu thanh toán.
     * (Sẽ được triển khai ở các bước sau)
     */
    public function process(Request $request)
    {
        // TODO: Validate dữ liệu địa chỉ
        // TODO: Gọi OrderService để tạo đơn hàng
        // TODO: Tạo Stripe Payment Intent
        // TODO: Trả về client_secret cho frontend

        dd($request->all()); // dump data để check
    }
    public function success()
    {
        return view('checkout.success');
    }
}
