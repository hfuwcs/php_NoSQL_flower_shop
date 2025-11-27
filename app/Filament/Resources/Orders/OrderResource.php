<?php

namespace App\Filament\Resources\Orders;

use App\Filament\Resources\Orders\Pages\CreateOrder;
use App\Filament\Resources\Orders\Pages\EditOrder;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Filament\Resources\Orders\Pages\ViewOrder;
use App\Filament\Resources\Orders\Schemas\OrderForm;
use App\Filament\Resources\Orders\Schemas\OrderInfolist;
use App\Models\Order;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\SelectAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
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
                Section::make('Order Details')
                    ->schema([
                        TextInput::make('user.name')
                            ->label('Customer')
                            ->disabled(),
                        TextInput::make('user.email')
                            ->label('Customer Email')
                            ->disabled(),
                        Select::make('status')
                            ->label('Order Status')
                            ->options([
                                'pending' => 'Pending',
                                'processing' => 'Processing',
                                'shipped' => 'Shipped',
                                'completed' => 'Completed',
                                'failed' => 'Failed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required()
                            ->native(false),
                        TextInput::make('total_amount')
                            ->label('Total Amount')
                            ->prefix('$')
                            ->disabled(),
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
            ->recordActions([
                ViewAction::make(),

                Action::make('updateStatus')
                ->label('Update Status')
                ->icon('heroicon-o-arrow-path')
                ->color('info')
                ->hidden(fn (Order $record): bool => in_array($record->status, ['completed', 'cancelled', 'failed']))
                ->schema([
                    Select::make('status')
                        ->label('New Status')
                        ->options([
                            'processing' => 'Processing',
                            'shipped' => 'Shipped',
                            'completed' => 'Completed',
                            'cancelled' => 'Cancelled',
                        ])
                        ->default(fn (Order $record): string => $record->status) 
                        ->required(),
                ])
                ->action(function (Order $record, array $data): void {
                    $newStatus = $data['status'];
                    $record->update([
                        'status' => $newStatus,
                    ]);
                    
                    // Sync delivery_status của các OrderItem theo Order status
                    $itemStatus = match ($newStatus) {
                        'processing' => 'processing',
                        'shipped' => 'shipped',
                        'completed' => 'delivered',
                        'cancelled' => 'cancelled',
                        default => null,
                    };
                    
                    if ($itemStatus) {
                        foreach ($record->items as $item) {
                            $updateData = ['delivery_status' => $itemStatus];
                            
                            // Nếu chuyển sang delivered, set review_deadline_at
                            if ($itemStatus === 'delivered' && is_null($item->review_deadline_at)) {
                                $updateData['delivered_at'] = now();
                                $updateData['review_deadline_at'] = now()->addDays(7);
                            }
                            
                            $item->update($updateData);
                        }
                    }
                    
                    Notification::make()
                        ->title('Order status updated successfully')
                        ->success()
                        ->send();
                })
                ->modalSubmitActionLabel('Update Status')
                ->modalWidth('md'),
        ])
            ->toolbarActions([
                // ...
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
