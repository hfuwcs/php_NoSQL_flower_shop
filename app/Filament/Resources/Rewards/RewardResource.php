<?php

namespace App\Filament\Resources\Rewards;

use App\Filament\Resources\Rewards\Pages\CreateReward;
use App\Filament\Resources\Rewards\Pages\EditReward;
use App\Filament\Resources\Rewards\Pages\ListRewards;
use App\Filament\Resources\Rewards\Schemas\RewardForm;
use App\Filament\Resources\Rewards\Tables\RewardsTable;
use App\Models\Reward;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RewardResource extends Resource
{
    protected static ?string $model = Reward::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Rewards';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('name')->required()->columnSpanFull(),
                Textarea::make('description')->columnSpanFull(),
                TextInput::make('point_cost')->required()->numeric()->label('Point Cost'),
                Select::make('type')
                    ->options([
                        'coupon' => 'Coupon',
                        'free_shipping' => 'Free Shipping',
                        'physical_gift' => 'Physical Gift',
                    ])
                    ->required(),
                KeyValue::make('reward_details')
                    ->label('Reward Details')
                    ->helperText('Ex: For coupon -> {"type":"percent", "value":10}. For gift -> {"product_sku":"GIFT-SKU-01"}')
                    ->columnSpanFull(),
                Toggle::make('is_active')->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('type')->badge(),
                TextColumn::make('point_cost')->numeric()->sortable(),
                IconColumn::make('is_active')->boolean(),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
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
            'index' => ListRewards::route('/'),
            'create' => CreateReward::route('/create'),
            'edit' => EditReward::route('/{record}/edit'),
        ];
    }
}
