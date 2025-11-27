<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;

class FixOrderItemsDeliveryStatus extends Command
{
    protected $signature = 'fix:order-items-delivery-status';
    protected $description = 'Fix delivery_status and review_deadline_at for order items based on order status';

    public function handle()
    {
        $orders = Order::whereIn('status', ['completed', 'shipped'])->get();
        $count = 0;

        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                $newStatus = $order->status === 'completed' ? 'delivered' : 'shipped';
                $updateData = ['delivery_status' => $newStatus];
                
                if ($newStatus === 'delivered' && is_null($item->review_deadline_at)) {
                    $updateData['delivered_at'] = now();
                    $updateData['review_deadline_at'] = now()->addDays(7);
                }
                
                $item->update($updateData);
                $count++;
            }
        }

        $this->info("Updated {$count} order items successfully!");
        return Command::SUCCESS;
    }
}
