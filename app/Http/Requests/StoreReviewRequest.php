<?php

namespace App\Http\Requests;

use App\Models\OrderItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreReviewRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $orderItem = OrderItem::find($this->input('order_item_id'));

        if (!$orderItem || !Auth::check()) {
            return false;
        }

        // Kiểm tra người dùng có phải chủ sở hữu order không
        $isOwner = (string) $orderItem->order->user_id === (string) Auth::id();

        // Kiểm tra đã giao hàng chưa (chỉ 'delivered' mới được review theo logic trong view)
        $isDelivered = $orderItem->delivery_status === 'delivered';

        // Kiểm tra chưa review
        $isNotReviewedYet = is_null($orderItem->review_id);
        
        // Kiểm tra còn trong thời hạn review (nếu không có deadline thì mặc định cho phép)
        $isWithinReviewPeriod = is_null($orderItem->review_deadline_at) 
            || now()->lte($orderItem->review_deadline_at);

        return $isOwner && $isDelivered && $isNotReviewedYet && $isWithinReviewPeriod;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:5000'],
            'order_item_id' => ['required', 'string', 'exists:order_items,_id'],
        ];
    }
}