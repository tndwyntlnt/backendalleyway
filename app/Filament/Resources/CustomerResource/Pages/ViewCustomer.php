<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

use App\Filament\Resources\CustomerResource\RelationManagers\OrdersRelationManager;
use App\Filament\Resources\CustomerResource\RelationManagers\CustomerRewardsRelationManager;

class ViewCustomer extends ViewRecord
{
    protected static string $resource = CustomerResource::class;
    public function getRelationManagers(): array
    {
        return [
            OrdersRelationManager::class,
            CustomerRewardsRelationManager::class,
        ];
    }
}