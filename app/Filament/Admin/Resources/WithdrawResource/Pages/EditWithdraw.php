<?php

namespace App\Filament\Admin\Resources\WithdrawResource\Pages;

use App\Filament\Admin\Resources\WithdrawResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWithdraw extends EditRecord
{
    protected static string $resource = WithdrawResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
