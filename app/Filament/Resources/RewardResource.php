<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RewardResource\Pages;
use App\Filament\Resources\RewardResource\RelationManagers;
use App\Models\Reward;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\Storage;
use Filament\Tables\Columns\ImageColumn;

class RewardResource extends Resource
{
    protected static ?string $model = Reward::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(3)->schema([
                    Group::make()->schema([
                        Section::make('Reward Details')->schema([
                            TextInput::make('name')
                                ->required()
                                ->maxLength(255),

                            FileUpload::make('image_url')
                                ->label('Reward Image')
                                ->image()
                                ->disk('s3')
                                ->directory('rewards')
                                ->visibility('public'),

                            TextInput::make('points_required')
                                ->label('Points Required')
                                ->required()
                                ->numeric()
                                ->helperText('Jumlah poin untuk me-redeem reward ini.'),

                            Textarea::make('description')
                                ->columnSpanFull(),
                        ])
                    ])->columnSpan(2),

                    Group::make()->schema([
                        Section::make('Status')->schema([
                            Toggle::make('is_active')
                                ->label('Active')
                                ->helperText('Reward akan tampil di aplikasi.')
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
                    ->circular()
                    ->getStateUsing(function ($record) {
                        if (! $record->image_url) {
                            return null;
                        }

                        if (str_starts_with($record->image_url, 'http://') ||
                            str_starts_with($record->image_url, 'https://')) {
                            return $record->image_url;
                        }

                        return Storage::disk('s3')->url($record->image_url);
                    }),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('points_required')
                    ->label('Points Required')
                    ->numeric() 
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
            'index' => Pages\ListRewards::route('/'),
            'create' => Pages\CreateReward::route('/create'),
            'edit' => Pages\EditReward::route('/{record}/edit'),
        ];
    }
}
