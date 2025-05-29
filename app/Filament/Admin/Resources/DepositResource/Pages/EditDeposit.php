<?php

namespace App\Filament\Admin\Resources\DepositResource\Pages;

use App\Filament\Admin\Resources\DepositResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDeposit extends EditRecord
{
    protected static string $resource = DepositResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
