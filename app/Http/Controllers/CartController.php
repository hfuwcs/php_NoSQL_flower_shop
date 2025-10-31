<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    // Dependency Injection: inject CartService vào controller.
    public function __construct(protected CartService $cartService) {}

    public function index(Request $request)
    {
        $cartData = $this->cartService->getCartContent($request->user());

        return view('cart.index', [
            'cartItems' => $cartData['items'],
            'cartTotal' => $cartData['total'],
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
}
