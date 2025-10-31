<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;
use UnexpectedValueException;

class StripeWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
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
                $payload, $sig_header, $endpoint_secret
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

        switch ($event->type) {
            case 'payment_intent.succeeded':
                $paymentIntent = $event->data->object; // Get  payment intent object
                
                $orderId = $paymentIntent->metadata->order_id;
                
                $order = Order::find($orderId);
                
                if ($order && $order->status === 'pending') {
                    // Update order status
                    $order->status = 'processing';
                    // Lưu thông tin thanh toán
                    $order->payment_details = [
                        'payment_intent_id' => $paymentIntent->id,
                        'payment_method' => $paymentIntent->payment_method,
                    ];
                    $order->save();
                    
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
}