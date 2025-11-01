<?php

namespace App\Filament\Resources;
use App\Models\Product;

use Filament\Forms;
use Filament\Tables;
use App\Models\Order;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\OrderResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\OrderResource\Pages\ViewOrder;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Filament\Resources\OrderResource\RelationManagers\OrderItemsRelationManager;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(3)->schema([
                    Group::make()->schema([
                        Section::make('Order Items')->schema([
                            Repeater::make('orderItems')
                                ->relationship() 
                                ->schema([
                                    Select::make('product_id')
                                        ->label('Product')
                                        ->options(Product::where('is_active', true)->pluck('name', 'id'))
                                        ->searchable()
                                        ->required()
                                        ->reactive() 
                                        ->afterStateUpdated(function ($state, Set $set) {
                                            $product = Product::find($state);
                                            if ($product) {
                                                $set('price_per_item', $product->price);
                                            }
                                        }),

                                    TextInput::make('quantity')
                                        ->numeric()
                                        ->required()
                                        ->default(1)
                                        ->minValue(1)
                                        ->reactive(), 

                                    TextInput::make('price_per_item')
                                        ->label('Price per Item')
                                        ->numeric()
                                        ->required()
                                        ->disabled() 
                                        ->dehydrated(), 
                                ])
                                ->columns(3)
                                ->reactive()

                                ->afterStateUpdated(function (Get $get, Set $set) { 
                                    $items = $get('orderItems');
                                    $total = 0;
                                    foreach ($items as $item) {
                                        if (!empty($item['price_per_item']) && !empty($item['quantity'])) {
                                            $total += $item['price_per_item'] * $item['quantity'];
                                        }
                                    }
                                    $set('total_amount', $total);
                                    
                                    $points = floor($total / 20000) * 10; 
                                    $set('points_earned', $points);
                                }),
                        ])
                    ])->columnSpan(2),

                    Group::make()->schema([
                        Section::make('Transaction Details')->schema([
                            TextInput::make('total_amount')
                                ->label('Total Amount')
                                ->numeric()
                                ->prefix('Rp')
                                ->disabled() 
                                ->dehydrated() 
                                ->required(),

                            Placeholder::make('points_earned')
                                ->label('Points Earned')
                                ->content(function (Get $get) { 
                                    return $get('points_earned') ?? 0;
                                }),

                            TextInput::make('transaction_code')
                                ->disabled()
                                ->dehydrated(),

                            Select::make('status')
                                ->options([
                                    'unclaimed' => 'Unclaimed',
                                    'claimed' => 'Claimed',
                                ])
                                ->default('unclaimed')
                                ->disabled()
                                ->dehydrated(),
                        ])
                    ])->columnSpan(1),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('transaction_code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('points_earned')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('claimed_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            OrderItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
