<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

use App\Filament\Resources\OrderResource\RelationManagers\OrderItemsRelationManager;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    public function getRelationManagers(): array
    {
        return [
            OrderItemsRelationManager::class,
        ];
    }
}