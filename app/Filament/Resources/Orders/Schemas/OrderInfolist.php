<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)->schema([
                    // Cột chính - Order Details
                    Section::make('Order Details')
                        ->schema([
                            TextEntry::make('user.name')->label('Customer'),
                            TextEntry::make('user.email')->label('Customer Email'),
                            TextEntry::make('status')
                                ->badge()
                                ->color(fn(string $state): string => match ($state) {
                                    'pending', 'failed' => 'danger',
                                    'processing' => 'warning',
                                    'shipped', 'completed' => 'success',
                                    default => 'gray',
                                }),
                            TextEntry::make('total_amount')->money('usd'),
                            TextEntry::make('created_at')->dateTime(),
                        ])->columnSpan(2),

                    // Cột bên phải - Shipping Address
                    Section::make('Shipping Address')
                        ->schema([
                            TextEntry::make('shipping_address.name')->label('Recipient'),
                            TextEntry::make('shipping_address.address')->label('Address'),
                            TextEntry::make('shipping_address.city')->label('City'),
                            TextEntry::make('shipping_address.phone')->label('Phone'),
                        ])->columnSpan(1),
                ]),
                
                // Section Order Items
                Section::make('Order Items')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->schema([
                                Grid::make(4)->schema([
                                    TextEntry::make('product_name')
                                        ->label('Product')
                                        ->columnSpan(2),
                                    TextEntry::make('quantity')
                                        ->label('Qty'),
                                    TextEntry::make('price_at_purchase')
                                        ->label('Price')
                                        ->money('usd'),
                                ]),
                            ])
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
