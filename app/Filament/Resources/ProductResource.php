<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Forms\Components\Repeater;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(3)->schema([
                    Group::make()->schema([
                        Section::make('Product Details')->schema([
                            TextInput::make('name')
                                ->required()
                                ->maxLength(255),

                            TextInput::make('price')
                                ->label('Starting Price')
                                ->required()
                                ->numeric()
                                ->prefix('Rp')
                                ->helperText('Harga mulai dari. Untuk harga tiap ukuran, isi di bagian Cup Sizes.'),

                            Textarea::make('description')
                                ->columnSpanFull(),

                            Repeater::make('variants')
                                ->label('Cup Sizes & Prices')
                                ->relationship()
                                ->schema([
                                    TextInput::make('name')
                                        ->label('Cup Size')
                                        ->placeholder('Regular')
                                        ->required()
                                        ->maxLength(255),

                                    TextInput::make('price')
                                        ->label('Price')
                                        ->required()
                                        ->numeric()
                                        ->prefix('Rp'),

                                    TextInput::make('sort_order')
                                        ->label('Order')
                                        ->numeric()
                                        ->default(0),

                                    Toggle::make('is_active')
                                        ->label('Active')
                                        ->default(true),
                                ])
                                ->columns(4)
                                ->defaultItems(1)
                                ->reorderable()
                                ->collapsible()
                                ->itemLabel(fn (array $state): ?string => $state['name'] ?? 'Cup Size'),
                        ])
                    ])->columnSpan(2),

                    Group::make()->schema([
                        Section::make('Image & Status')->schema([
                            FileUpload::make('image_url')
                                ->label('Product Image')
                                ->image()
                                ->disk('s3')
                                ->directory('products')
                                ->visibility('public'),

                            Toggle::make('is_active')
                                ->label('Active')
                                ->helperText('Produk akan tampil di aplikasi.')
                                ->default(true)
                                ->required(),
                        ])
                    ])->columnSpan(1),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_url')
                    ->label('Image')
                    ->circular(),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('price')
                    ->label('Starting Price')
                    ->money('IDR')
                    ->sortable(),

                ToggleColumn::make('is_active')
                    ->label('Active'),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                \Filament\Tables\Actions\ViewAction::make(),
                \Filament\Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                \Filament\Tables\Actions\BulkActionGroup::make([
                    \Filament\Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
