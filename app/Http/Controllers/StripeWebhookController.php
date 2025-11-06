<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Services\PointService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;
use UnexpectedValueException;

class StripeWebhookController extends Controller
{
    public function __construct(protected PointService $pointService) {}
    public function handleWebhook(Request $request)
    {
        #region verify Webhook
        $endpoint_secret = config('stripe.webhook_secret');
        $payload = $request->getContent();
        $sig_header = $request->server('HTTP_STRIPE_SIGNATURE');
        $event = null;

        Log::info('Webhook received', [
            'endpoint_secret' => $endpoint_secret ? 'Set' : 'Not set',
            'sig_header' => $sig_header ? 'Present' : 'Missing',
            'payload_length' => strlen($payload)
        ]);

        try {
            // Verify Signature từ Stripe
            $event = Webhook::constructEvent(
                $payload,
                $sig_header,
                $endpoint_secret
            );
        } catch (UnexpectedValueException $e) {
            // Invalid payload
            Log::error('Webhook payload error: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid payload', 'message' => $e->getMessage()], 400);
        } catch (SignatureVerificationException $e) {
            // Invalid signature
            Log::error('Webhook signature error: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid signature', 'message' => $e->getMessage()], 400);
        }
        #endregion

        switch ($event->type) {
            case 'payment_intent.succeeded':
                $paymentIntent = $event->data->object;

                $orderId = $paymentIntent->metadata->order_id ?? null;
                if (!$orderId) {
                    Log::error('Webhook payment_intent.succeeded missing order_id in metadata.', ['payment_intent_id' => $paymentIntent->id]);
                    break;
                }

                $order = Order::with('user')->find($orderId);

                if ($order && $order->status === 'pending') {
                    // Cập nhật trạng thái đơn hàng
                    $order->status = 'processing';
                    $order->payment_details = [
                        'payment_intent_id' => $paymentIntent->id,
                        'payment_method' => $paymentIntent->payment_method,
                    ];
                    $order->save();

                    $this->deductStockForOrder($order);

                    if ($order->user) {
                        $this->pointService->addPointsForAction(
                            $order->user,
                            'order_completed',
                            $order
                        );
                    } else {
                        Log::info('Order completed but no user associated for awarding points.', ['order_id' => $order->id]);
                    }

                    // TODO: Dispatch các job khác (gửi email, cập nhật kho, etc.)
                    // SendOrderConfirmationEmailJob::dispatch($order);
                }
                break;

            case 'payment_intent.payment_failed':
                $paymentIntent = $event->data->object;
                $orderId = $paymentIntent->metadata->order_id;
                $order = Order::find($orderId);

                if ($order && $order->status === 'pending') {
                    $order->status = 'failed';
                    $order->save();
                    // TODO: Gửi email thông báo cho khách hàng
                }
                break;

            // ... xử lý các loại sự kiện khác nếu muôna

            default:
        }

        //Trả về response 200 để báo cho Stripee
        return response()->json(['status' => 'success']);
    }

    /**
     * Trừ kho cho các sản phẩm trong một đơn hàng đã được xác nhận thanh toán.
     *
     * @param Order $order
     * @return void
     */
    private function deductStockForOrder(Order $order): void
    {
        $order->loadMissing('items');

        foreach ($order->items as $item) {

            $updatedCount = Product::where('_id', $item->product_id)
                ->where('stock_quantity', '>=', $item->quantity)
                ->decrement('stock_quantity', $item->quantity);

            if ($updatedCount === 0) {
                Log::critical('OVERSALE DETECTED!', [
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity_ordered' => $item->quantity,
                ]);

                // TODO: Trong tương lai, có thể tự động hoàn tiền và thông báo cho khách hàng.
                // For now, logging is the most critical action.
            }
        }
    }
}
