<?php

namespace App\Filament\Resources\ReferralResource\Pages;

use App\Filament\Resources\ReferralResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditReferral extends EditRecord
{
    protected static string $resource = ReferralResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
