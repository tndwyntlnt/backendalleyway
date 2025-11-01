<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'orderItems';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('id')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Order Items')
            ->columns([
                TextColumn::make('product.name')
                    ->label('Product'),

                TextColumn::make('price_per_item')
                    ->money('IDR')
                    ->label('Price per Item'),
                
                TextColumn::make('quantity') 
                    ->label('Subtotal') 
                    ->numeric()
                    ->formatStateUsing(function ($record) { 
                        $subtotal = $record->quantity * $record->price_per_item;
                        return 'Rp ' . number_format($subtotal, 0, ',', '.');
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // \Filament\Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                // \Filament\Tables\Actions\EditAction::make(),
                // \Filament\Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                // \Filament\Tables\Actions\BulkActionGroup::make([
                //     \Filament\Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
