<?php

namespace App\Filament\Common\Resources\WithdrawResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Common\Resources\WithdrawResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWithdraw extends EditRecord
{
    protected static string $resource = WithdrawResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
