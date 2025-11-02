<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CartService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    // Dependency Injection: inject CartService vào controller.
    public function __construct(protected CartService $cartService) {}

    public function index(Request $request)
    {
        $cartContent = $this->cartService->getCartContent($request->user());

        return view('cart.index', [
            'cart' => $cartContent
        ]);
    }

    public function add(Request $request, Product $product)
    {
        $quantity = $request->input('quantity', 1);

        $user = $request->user();

        $this->cartService->addProduct($user, $product, (int)$quantity);

        return back()->with('success', "{$product->name} has been added to your cart!");
    }

    public function update(Request $request, string $productId)
    {
        $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $this->cartService->updateItemQuantity(
            $request->user(),
            $productId,
            (int)$request->input('quantity')
        );

        return back()->with('success', 'Cart updated successfully!');
    }

    public function remove(Request $request, string $productId)
    {
        $this->cartService->removeItem($request->user(), $productId);

        return back()->with('success', 'Item removed from cart!');
    }

    public function applyCoupon(Request $request)
    {
        // 1. Validate input
        $validated = $request->validate([
            'coupon_code' => ['required', 'string', 'max:255'],
        ]);

        try {
            $this->cartService->applyCoupon(Auth::user(), $validated['coupon_code']);
            
            $updatedCartContent = $this->cartService->getCartContent(Auth::user());

            return response()->json([
                'success' => true,
                'message' => 'Coupon applied successfully!',
                'cart' => $updatedCartContent,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422); 
        }
    }

}
