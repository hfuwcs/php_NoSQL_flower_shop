<?php

namespace App\Console\Commands;

use App\Models\OrderItem;
use Illuminate\Console\Command;

class AutoCloseReviewWindows extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:auto-close-review-windows';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Finds and closes the review window for delivered items that have passed their deadline.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Closing expired review windows...');

        $itemsToClose = OrderItem::where('delivery_status', 'delivered')
            ->whereNull('review_id')
            ->where('review_deadline_at', '<', now()) // Đã quá hạn
            ->get();

        foreach ($itemsToClose as $item) {
            $item->delivery_status = 'completed_by_system';
            $item->save();
        }

        $this->info("Successfully closed " . $itemsToClose->count() . " review windows.");
    }
}
