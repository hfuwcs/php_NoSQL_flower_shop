<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function __construct(protected CartService $cartService)
    {
    }

    /**
     * Hiển thị trang checkout.
     */
    public function index(Request $request)
    {
        $cartData = $this->cartService->getCartContent($request->user());

        // Không cho phép checkout nếu giỏ hàng trống.
        if (empty($cartData['items'])) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty. Please add products before proceeding to checkout.');
        }

        return view('checkout.index', [
            'cartItems' => $cartData['items'],
            'cartTotal' => $cartData['total'],
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
}