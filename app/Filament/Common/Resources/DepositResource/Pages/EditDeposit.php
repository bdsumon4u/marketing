<?php

namespace App\Filament\Common\Resources\DepositResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Common\Resources\DepositResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDeposit extends EditRecord
{
    protected static string $resource = DepositResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
