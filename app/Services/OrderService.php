<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(protected CartService $cartService)
    {
    }

    /**
     * Tạo một đơn hàng từ giỏ hàng hiện tại của người dùng.
     * Toàn bộ quá trình được bọc trong một transaction.
     *
     * @param User $user
     * @param array $shippingDetails Dữ liệu địa chỉ từ form.
     * @return Order
     * @throws \Exception
     */
    public function createOrderFromCart(User $user, array $shippingDetails): Order
    {
        //TRANSACTION: DB::transaction
        return DB::transaction(function () use ($user, $shippingDetails) {
            $cartData = $this->cartService->getCartContent($user);

            if (empty($cartData['items'])) {
                throw new \Exception('Cannot create order from an empty cart.');
            }

            // Tạo record Order chính
            $order = Order::create([
                'user_id' => $user->id,
                'status' => 'pending', //default
                'total_amount' => $cartData['total'],
                'shipping_address' => $shippingDetails,
            ]);

            $orderItemsData = [];
            foreach ($cartData['items'] as $cartItem) {
                $orderItemsData[] = [
                    'order_id' => $order->id,
                    'product_id' => $cartItem['product_id'],
                    'quantity' => $cartItem['quantity'],
                    'price_at_purchase' => $cartItem['price'],
                    'product_name' => $cartItem['product_name'],
                ];
            }

            // add all items into orders
            OrderItem::insert($orderItemsData);

            // Remove user's cart when succusfylly create an order
            $user->cart()->delete();
            
            return $order;
        });
    }
}