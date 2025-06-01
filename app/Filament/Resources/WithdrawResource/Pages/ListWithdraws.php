<?php

namespace App\Filament\Resources\WithdrawResource\Pages;

use App\Filament\Resources\WithdrawResource;
use App\Models\User;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ListRecords;

class ListWithdraws extends ListRecords
{
    protected static string $resource = WithdrawResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->slideOver()
                ->modalWidth('md')
                ->modalHeading('Request Withdraw')
                ->createAnother(false)
                ->modalSubmitActionLabel('Request')
                ->action(function (array $data, Actions\Action $action) {
                    $user = value(fn (): User => Filament::auth()->user());
                    $wallet = $user->getOrCreateWallet('earning');

                    if ($wallet->balanceFloat < $data['amount']) {
                        $action->failureNotificationTitle('Insufficient balance')->failure();

                        return $action->halt();
                    }

                    $record = $wallet->withdrawFloat($data['amount'], [
                        'action' => 'withdraw',
                        'message' => 'Withdraw request to ' . $data['bkash_number'],
                        'bkash_number' => $data['bkash_number'],
                    ], false);

                    $action->successNotificationTitle('Withdraw request sent')->success();

                    return $record;
                }),
        ];
    }
}
