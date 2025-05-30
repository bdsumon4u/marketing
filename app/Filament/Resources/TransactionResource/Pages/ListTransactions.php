<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTransactions extends ListRecords
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
            Actions\Action::make('addFund') // open add-fund modal
                ->label('Add Fund')
                ->action(fn () => $this->dispatch('open-modal', id: 'add-fund-modal')),
        ];
    }
}
