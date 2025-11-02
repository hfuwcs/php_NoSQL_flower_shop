<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\User;
use Exception;

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

        $defaultResponse = [
            'items' => [],
            'subtotal' => 0,
            'applied_coupon' => null,
            'discount_amount' => 0,
            'final_total' => 0,
        ];

        if (!$cart || empty($cart->items)) {
            return $defaultResponse;
        }

        $items = $cart->items;
        $subtotal = 0;

        foreach ($items as $item) {
            $price = is_numeric($item['price']) ? (float) $item['price'] : 0;
            $quantity = is_numeric($item['quantity']) ? (int) $item['quantity'] : 0;
            $subtotal += $price * $quantity;
        }

        $appliedCoupon = $cart->applied_coupon;
        $discountAmount = 0;

        if ($appliedCoupon) {
            if ($appliedCoupon['type'] === 'percent') {
                $discountAmount = ($subtotal * $appliedCoupon['value']) / 100;
            } elseif ($appliedCoupon['type'] === 'fixed') {
                $discountAmount = $appliedCoupon['value'];
            }
        }

        $finalTotal = max(0, $subtotal - $discountAmount);

        return [
            'items' => $items,
            'subtotal' => round($subtotal, 2),
            'applied_coupon' => $appliedCoupon,
            'discount_amount' => round($discountAmount, 2),
            'final_total' => round($finalTotal, 2),
        ];
    }

    /**
     * Cập nhật số lượng của một item trong giỏ hàng.
     * Nếu số lượng <= 0, item sẽ bị xóa.
     *
     * @param User $user
     * @param string $productId ID của sản phẩm cần cập nhật.
     * @param int $quantity Số lượng mới.
     * @return Cart|null
     */
    public function updateItemQuantity(User $user, string $productId, int $quantity): ?Cart
    {
        $cart = Cart::where('user_id', $user->id)->first();

        if (!$cart) {
            return null;
        }

        $items = $cart->items ?? [];
        $productFound = false;

        foreach ($items as $index => &$item) { //reference
            if ($item['product_id'] === $productId) {
                if ($quantity > 0) {
                    $item['quantity'] = $quantity;
                } else {
                    // Nếu số lượng mới là 0 hoặc âm, xóa item
                    unset($items[$index]);
                }
                $productFound = true;
                break;
            }
        }

        if ($productFound) {
            $cart->items = array_values($items);
            $cart->save();
        }

        return $cart;
    }

    /**
     * Xóa một item khỏi giỏ hàng.
     *
     * @param User $user
     * @param string $productId ID của sản phẩm cần xóa.
     * @return Cart|null
     */
    public function removeItem(User $user, string $productId): ?Cart
    {
        $cart = Cart::where('user_id', $user->id)->first();

        if (!$cart) {
            return null;
        }

        $items = $cart->items ?? [];

        // Lọc ra mảng mới không chứa sản phẩm cần xóa
        $newItems = array_filter($items, function ($item) use ($productId) {
            return $item['product_id'] !== $productId;
        });

        $cart->items = array_values($newItems);
        $cart->save();

        return $cart;
    }

    /**
     * Áp dụng một mã giảm giá vào giỏ hàng của người dùng.
     *
     * @param User $user
     * @param string $couponCode
     * @return Cart
     * @throws Exception
     */
    public function applyCoupon(User $user, string $couponCode): Cart
    {
        $cart = Cart::where('user_id', $user->id)->first();
        if (!$cart || empty($cart->items)) {
            throw new Exception('Cannot apply coupon to an empty cart.');
        }

        $coupon = Coupon::where('code', $couponCode)->first();
        if (!$coupon) {
            throw new Exception('Coupon code is invalid.');
        }

        if (!$coupon->isValid()) {
            throw new Exception('This coupon is expired or has reached its usage limit.');
        }

        $cart->applied_coupon = [
            'code' => $coupon->code,
            'type' => $coupon->type,
            'value' => $coupon->value,
        ];

        $cart->save();

        return $cart;
    }
}
