<?php

namespace App\Filament\Resources\CustomerResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('transaction_code')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Order History') 
            ->columns([
                TextColumn::make('transaction_code')
                    ->label('Transaction Code')
                    ->searchable(),

                TextColumn::make('total_amount')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('points_earned')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('status')
                    ->badge() 
                    ->color(fn (string $state): string => match ($state) {
                        'unclaimed' => 'warning',
                        'claimed' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('claimed_at')
                    ->label('Claimed At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // 
            ])
            ->actions([
                \Filament\Tables\Actions\ViewAction::make()
                    ->url(fn (\App\Models\Order $record): string => \App\Filament\Resources\OrderResource::getUrl('view', ['record' => $record])),
            ])
            ->bulkActions([
                //
            ]);
    }
}
