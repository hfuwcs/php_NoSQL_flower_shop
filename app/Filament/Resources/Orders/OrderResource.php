<?php

namespace App\Filament\Resources\Orders;

use App\Filament\Resources\Orders\Pages\CreateOrder;
use App\Filament\Resources\Orders\Pages\EditOrder;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Filament\Resources\Orders\Pages\ViewOrder;
use App\Filament\Resources\Orders\Schemas\OrderForm;
use App\Filament\Resources\Orders\Schemas\OrderInfolist;
use App\Filament\Resources\Orders\Tables\OrdersTable;
use App\Models\Order;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Orders';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Grid::make(3)->schema([
                    // Cột chính
                    Section::make('Order Details')
                        ->schema([
                            TextEntry::make('user.name')->label('Customer'),
                            TextEntry::make('user.email')->label('Customer Email'),
                            TextEntry::make('status')
                                ->badge()
                                ->color(fn(int $state): string => match ($state) {
                                    1, 2 => 'danger',
                                    3 => 'warning',
                                    4, 5 => 'success',
                                    default => 'gray',
                                }),
                            TextEntry::make('total_amount')->money('usd'),
                            TextEntry::make('created_at')->dateTime(),
                        ])->columnSpan(2),

                    // Cột bên phải
                    Section::make('Shipping Address')
                        ->schema([
                            TextEntry::make('shipping_address.name')->label('Recipient'),
                            TextEntry::make('shipping_address.address'),
                            TextEntry::make('shipping_address.city'),
                            TextEntry::make('shipping_address.phone'),
                        ])->columnSpan(1),
                ]),
                Section::make('Order Items')
                    ->schema([
                        // Todo: thêm Repeater hiển thị các item
                    ]),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return OrderInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('_id')->label('Order ID')->searchable(),
                TextColumn::make('user.name')->label('Customer')->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending', 'failed' => 'danger',
                        'processing' => 'warning',
                        'shipped', 'completed' => 'success',
                        default => 'gray',
                    })
                    ->searchable(),
                TextColumn::make('total_amount')
                    ->money('usd')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            // ...
            ->recordActions([
                ViewAction::make(),
                //todo
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrders::route('/'),
            'create' => CreateOrder::route('/create'),
            'view' => ViewOrder::route('/{record}'),
            'edit' => EditOrder::route('/{record}/edit'),
        ];
    }
}
