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
            $cartContent = $this->cartService->getCartContent($user);

            if (empty($cartContent['items'])) {
                throw new \Exception('Cannot create order from an empty cart.');
            }

            // Tạo record Order chính
            $order = Order::create([
                'user_id' => $user->id,
                'status' => 'pending', //default
                'total_amount' => $cartContent['final_total'],
                'shipping_address' => $shippingDetails,
            ]);

            $orderItemsData = [];
            foreach ($cartContent['items'] as $cartItem) {
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

    /**
     * Tạo một đơn hàng "pending" từ giỏ hàng mà không cần địa chỉ giao hàng.
     * Dùng để khởi tạo quy trình thanh toán.
     */
    public function createPendingOrderFromCart(User $user): ?Order
    {
        $existingPendingOrder = Order::where('user_id', $user->id)->where('status', 'pending')->first();
        if ($existingPendingOrder) {
            return $existingPendingOrder;
        }

        $cartContent = $this->cartService->getCartContent($user);
        //dd($cartContent);
        if (empty($cartContent['items'])) {
            return null;
        }

        return DB::transaction(function () use ($user, $cartContent) {
            $order = Order::create([
                'user_id' => $user->id,
                'status' => 'pending',
                'total_amount' => $cartContent['final_total'],
                'shipping_address' => [],
            ]);

            $orderItemsData = [];
            foreach ($cartContent['items'] as $cartItem) {
                //TODO: Update các field khác
                $orderItemsData[] = [ 'order_id' => $order->id, /* ... các trường khác ... */ ];
            }
            OrderItem::insert($orderItemsData);

            $user->cart()->delete();
            return $order;
        });
    }
}