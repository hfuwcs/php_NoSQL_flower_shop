<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;

class CartService
{
    /**
     * Thêm một sản phẩm vào giỏ hàng của người dùng.
     * Nếu sản phẩm đã tồn tại, tăng số lượng.
     *
     * @param User $user Người dùng đang thực hiện hành động.
     * @param Product $product Sản phẩm cần thêm.
     * @param int $quantity Số lượng cần thêm.
     * @return Cart Giỏ hàng đã được cập nhật.
     */
    public function addProduct(User $user, Product $product, int $quantity = 1): Cart
    {
        // Tìm giỏ hàng của người dùng, nếu chưa có thì tạo mới.
        // firstOrCreate sẽ tìm một bản ghi khớp với điều kiện, nếu không thấy, nó sẽ tạo một bản ghi mới với các thuộc tính đã cho.
        $cart = Cart::firstOrCreate(['user_id' => $user->id]);

        // Lấy mảng 'items' hiện tại từ giỏ hàng.
        $items = $cart->items ?? [];
        
        $productIndex = -1;
        // Tìm kiếm xem sản phẩm đã có trong mảng 'items' chưa.
        foreach ($items as $index => $item) {
            if ($item['product_id'] === $product->id) {
                $productIndex = $index;
                break;
            }
        }

        if ($productIndex !== -1) {
            //1: Sản phẩm đã tồn tại trong giỏ hàng -> Tăng số lượng.
            $items[$productIndex]['quantity'] += $quantity;
        } else {
            //2: Sản phẩm chưa có trong giỏ hàng -> Thêm mới.
            $items[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'price' => $product->price,
                'quantity' => $quantity,
            ];
        }

        // Cập nhật lại mảng 'items' và lưu giỏ hàng.
        $cart->items = $items;
        $cart->save();

        return $cart;
    }

    /**
     * Lấy nội dung chi tiết và tổng tiền của giỏ hàng.
     *
     * @param User $user
     * @return array Chứa các items và tổng tiền.
     */
    public function getCartContent(User $user): array
    {
        $cart = Cart::where('user_id', $user->id)->first();

        if (!$cart || empty($cart->items)) {
            return [
                'items' => [],
                'total' => 0,
            ];
        }

        $items = $cart->items;
        $total = 0;

        foreach ($items as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        return [
            'items' => $items,
            'total' => $total,
        ];
    }
}