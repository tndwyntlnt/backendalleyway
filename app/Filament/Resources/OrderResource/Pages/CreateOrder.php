<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $total = $data['total_amount'] ?? 0;
        $points = floor($total / 20000) * 10; 
        $data['transaction_code'] = 'ALW-' . strtoupper(Str::random(6));
        $data['points_earned'] = $points;
        $data['status'] = 'unclaimed';

        return $data;
    }
}
