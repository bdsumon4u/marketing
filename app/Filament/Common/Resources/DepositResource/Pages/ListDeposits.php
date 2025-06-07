<?php

namespace App\Filament\Common\Resources\DepositResource\Pages;

use App\Filament\Common\Resources\DepositResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDeposits extends ListRecords
{
    protected static string $resource = DepositResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
