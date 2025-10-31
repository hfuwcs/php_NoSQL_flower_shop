<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestOrders extends BaseWidget
{
    protected static ?int $sort = 2;
    protected ?string $pollingInterval = '2m';
    
    public function table(Table $table): Table
    {
        return $table
            // Lấy 5 đơn hàng gần nhất
            ->query(OrderResource::getEloquentQuery()->latest()->limit(5))
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('Customer'),
                Tables\Columns\TextColumn::make('status')
                ->badge()
                ->color(fn(string $state): string => match ($state) {
                        'pending', 'failed' => 'danger',
                        'processing' => 'warning',
                        'shipped', 'completed' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('total_amount')->money('usd'),
            ])
            ->recordUrl(fn (Order $record): string => OrderResource::getUrl('view', ['record' => $record]));
    }
}