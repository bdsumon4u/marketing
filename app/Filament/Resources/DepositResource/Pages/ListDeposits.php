<?php

namespace App\Filament\Resources\DepositResource\Pages;

use App\Filament\Resources\DepositResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDeposits extends ListRecords
{
    protected static string $resource = DepositResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('addFund')
                ->label('Add Funds')
                ->icon('heroicon-o-plus')
                ->action(fn () => $this->dispatch('open-modal', id: 'add-fund-modal')),

        ];
    }
}
