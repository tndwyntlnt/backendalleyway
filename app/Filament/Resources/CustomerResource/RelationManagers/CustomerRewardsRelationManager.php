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
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;

class CustomerRewardsRelationManager extends RelationManager
{
    protected static string $relationship = 'customerRewards';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('status')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Reward Claim History')
            ->columns([
                TextColumn::make('reward.name')
                    ->label('Reward Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->badge() 
                    ->color(fn (string $state): string => match ($state) {
                        'unclaimed' => 'warning',
                        'used' => 'success',
                        'expired' => 'danger',
                        default => 'gray',
                    }),
                
                TextColumn::make('expires_at')
                    ->label('Expires At')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('created_at')
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
                Action::make('mark_as_used')
                    ->label('Mark as Used')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    
                    ->visible(fn ($record) => $record->status === 'unclaimed')
                    
                    ->requiresConfirmation()
                    ->modalHeading('Use this Reward?')
                    ->modalDescription('Are you sure you want to mark this reward as used? This cannot be undone.')
                    ->modalButton('Yes, use it')
                    
                    ->action(function ($record) {
                        $record->update(['status' => 'used']);
                        
                        Notification::make()
                            ->title('Reward Used')
                            ->body('The reward has been successfully marked as used.')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                //
            ]);
    }
}
