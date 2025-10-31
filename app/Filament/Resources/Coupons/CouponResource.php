<?php

namespace App\Filament\Resources\Coupons;

use App\Filament\Resources\Coupons\Pages\CreateCoupon;
use App\Filament\Resources\Coupons\Pages\EditCoupon;
use App\Filament\Resources\Coupons\Pages\ListCoupons;
use App\Filament\Resources\Coupons\Schemas\CouponForm;
use App\Filament\Resources\Coupons\Tables\CouponsTable;
use App\Models\Coupon;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Counpon';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('code')
                    ->required()
                    ->unique(ignoreRecord: true) // Unique
                    ->maxLength(255),
                Select::make('type')
                    ->options([
                        'fixed' => 'Fixed Amount',
                        'percent' => 'Percentage',
                    ])
                    ->required()
                    ->reactive(),
                TextInput::make('value')
                    ->required()
                    ->numeric(),
                DateTimePicker::make('expires_at'),
                TextInput::make('usage_limit')
                    ->label('Usage Limit (leave blank for unlimited)')
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->searchable(),
                TextColumn::make('type')
                    ->badge(),
                TextColumn::make('value')
                    ->formatStateUsing(
                        fn($record): string =>
                        $record->type === 'percent' ? "{$record->value}%" : '$' . number_format($record->value, 2)
                    )
                    ->label('Value'),
                TextColumn::make('expires_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('usage_count')
                    ->label('Usage')
                    ->formatStateUsing(
                        fn($record): string =>
                        "{$record->usage_count}" . ($record->usage_limit ? " / {$record->usage_limit}" : "")
                    ),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
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
            'index' => ListCoupons::route('/'),
            'create' => CreateCoupon::route('/create'),
            'edit' => EditCoupon::route('/{record}/edit'),
        ];
    }
}
