<?php

namespace App\Http\Controllers;

use App\Models\Order;
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
     */
    public function process(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
        ]);

        $order = Order::where('user_id', $request->user()->id)
            ->where('status', 'pending')
            ->latest()
            ->firstOrFail();

        $order->shipping_address = $validatedData;
        $order->save();

        return response()->json(['success' => true, 'message' => 'Address saved. Proceeding to payment.']);
    }
    public function success()
    {
        return view('checkout.success');
    }
}
