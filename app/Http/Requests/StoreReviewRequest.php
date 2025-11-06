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

        $isOwner = $orderItem->order->user_id === Auth::id();
        $isDelivered = $orderItem->delivery_status === 'delivered';
        $isNotReviewedYet = is_null($orderItem->review_id);
        $isPastReviewDate = now()->lte($orderItem->review_deadline_at);

        return $isOwner && $isDelivered && $isNotReviewedYet && $isPastReviewDate;
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