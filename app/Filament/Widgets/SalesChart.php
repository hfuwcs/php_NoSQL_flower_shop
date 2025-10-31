<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;

class SalesChart extends ChartWidget
{
    protected ?string $heading = 'Sales Chart';
    protected static ?int $sort = 1;

    protected function getData(): array
    {
        // Get completed orders from the last year
        $orders = Order::where('status', 'completed')
            ->where('created_at', '>=', now()->subYear())
            ->get();

        // Group by month and calculate totals
        $grouped = $orders->groupBy(function($order) {
            return $order->created_at->format('Y-m');
        })->map(function($monthOrders) {
            return $monthOrders->sum('total_amount');
        })->sortKeys();

        return [
            'datasets' => [
                [
                    'label' => 'Revenue',
                    'data' => $grouped->values()->toArray(),
                ],
            ],
            'labels' => $grouped->keys()->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
