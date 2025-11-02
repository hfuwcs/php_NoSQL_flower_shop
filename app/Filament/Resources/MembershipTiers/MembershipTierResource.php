<?php

namespace App\Filament\Resources\MembershipTiers;

use App\Filament\Resources\MembershipTiers\Pages\CreateMembershipTier;
use App\Filament\Resources\MembershipTiers\Pages\EditMembershipTier;
use App\Filament\Resources\MembershipTiers\Pages\ListMembershipTiers;
use App\Filament\Resources\MembershipTiers\Schemas\MembershipTierForm;
use App\Filament\Resources\MembershipTiers\Tables\MembershipTiersTable;
use App\Models\MembershipTier;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MembershipTierResource extends Resource
{
    protected static ?string $model = MembershipTier::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Membership Tier';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('min_points')
                    ->required()
                    ->numeric()
                    ->label('Minimum Points'),
                Textarea::make('benefits')
                    ->columnSpanFull()
                    ->helperText('Enter each benefit on a new line.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('min_points')
                    ->numeric()
                    ->sortable()
                    ->label('Minimum Points'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            ])
            ->defaultSort('min_points', 'asc');
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
            'index' => ListMembershipTiers::route('/'),
            'create' => CreateMembershipTier::route('/create'),
            'edit' => EditMembershipTier::route('/{record}/edit'),
        ];
    }
}
