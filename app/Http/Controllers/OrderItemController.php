<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderItemController extends Controller
{
    /**
     * Xử lý việc người dùng xác nhận đã nhận được một item.
     *
     * @param OrderItem $orderItem
     * @return RedirectResponse
     */
    public function confirmDelivery(OrderItem $orderItem): RedirectResponse
    {
        if ($orderItem->order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // Kiểm tra logic: Chỉ cho phép xác nhận nếu trạng thái hiện tại là 'shipped'.
        if ($orderItem->delivery_status !== 'shipped') {
            return back()->with('error', 'This item cannot be confirmed at this time.');
        }

        $orderItem->delivery_status = 'delivered';
        $orderItem->delivered_at = now();
        $orderItem->can_review_after = now()->addDays(7);
        $orderItem->save();

        return back()->with('success', 'Thank you for confirming the delivery of ' . $orderItem->product_name . '!');
    }
}
