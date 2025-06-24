<?php

namespace App\Filament\Common\Resources\DepositResource\Pages;

use App\Filament\Common\Resources\DepositResource;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ListRecords;

class ListDeposits extends ListRecords
{
    protected static string $resource = DepositResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('addFund')
                ->label('Add Funds')
                ->icon('heroicon-o-plus')
                ->action(fn () => $this->dispatch('open-modal', id: 'add-fund-modal'))
                ->visible(fn () => Filament::getCurrentOrDefaultPanel()->getId() === 'app'),
        ];
    }
}
