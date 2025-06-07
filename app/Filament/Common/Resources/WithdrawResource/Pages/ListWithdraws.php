<?php

namespace App\Filament\Common\Resources\WithdrawResource\Pages;

use App\Filament\Common\Resources\WithdrawResource;
use App\Models\User;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Number;

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

                    if ($wallet->balanceFloat - $user->pending_withdraw < $data['amount']) {
                        $action->failureNotification(
                            Notification::make()
                                ->title('Insufficient balance')
                                ->body('You have '.Number::currency($wallet->balanceFloat).' in your earning wallet but you have '.Number::currency($user->pending_withdraw).' in pending withdrawals.')
                                ->danger()
                        )
                            ->failure();

                        return $action->halt();
                    }

                    $record = $wallet->withdrawFloat($data['amount'], [
                        'action' => 'withdraw',
                        'message' => 'Withdraw request to '.$data['bkash_number'],
                        'bkash_number' => $data['bkash_number'],
                    ], false);

                    $user->increment('pending_withdraw', $data['amount'] * 100);

                    $action->successNotificationTitle('Withdraw request sent')->success();

                    return $record;
                })
                ->visible(fn () => Filament::getCurrentPanel()->getId() === 'app'),
        ];
    }
}
