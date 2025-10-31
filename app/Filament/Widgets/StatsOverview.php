<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected ?string $pollingInterval = '2m';
    protected function getStats(): array
    {
        // Thống kê Doanh thu
        // Chỉ tính tổng tiền của các đơn hàng đã hoàn thành
        $totalRevenue = Order::where('status', 'completed')->sum('total_amount');

        // Thống kê Đơn hàng mới
        // Đếm số đơn hàng được tạo trong 7 ngày qua
        $newOrdersCount = Order::where('created_at', '>=', now()->subDays(7))->count();

        // Thống kê Khách hàng mới
        // Đếm số user đăng ký trong 30 ngày qua
        $newCustomersCount = User::where('created_at', '>=', now()->subDays(30))->count();

        return [
            Stat::make('Total Revenue', '$' . number_format($totalRevenue, 2))
                ->description('All completed orders')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
            Stat::make('New Orders', $newOrdersCount)
                ->description('Last 7 days')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('warning'),
            Stat::make('New Customers', $newCustomersCount)
                ->description('Last 30 days')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),
        ];
    }
}