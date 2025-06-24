<?php

namespace App\Filament\Resources\TransferResource\Pages;

use App\Filament\Resources\TransferResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Number;

class ListTransfers extends ListRecords
{
    protected static string $resource = TransferResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Send Money')
                ->slideOver()
                ->modalWidth('md')
                ->modalHeading('Send Money')
                ->modalSubmitActionLabel('Send')
                ->extraModalFooterActions(function (Action $action) {
                    return $action->canCreateAnother() ? [
                        $action->makeModalSubmitAction('createAnother', arguments: ['another' => true])
                            ->label('Send and send another'),
                    ] : [];
                })
                ->using(function (array $data, Action $action) {
                    $sender = value(fn (): User => Filament::auth()->user());

                    if ($sender->balanceFloat < $data['amount']) {
                        $action->failureNotificationTitle('Insufficient balance')->failure();

                        return $action->halt();
                    }

                    if ($receiver = User::query()->where('username', $data['transfer_to'])->first()) {
                        $record = $sender->transferFloat($receiver, $data['amount'], [
                            'action' => 'transfer',
                            'message' => 'Transfer from #'.$sender->username.' to #'.$receiver->username,
                        ]);

                        $sender->increment('total_send', $data['amount'] * 100);
                        $receiver->increment('total_receive', $data['amount'] * 100);

                        Notification::make()
                            ->success()
                            ->title('Received money')
                            ->body('You\'ve received '.Number::currency($data['amount']).' from #'.$sender->username)
                            ->sendToDatabase($receiver);

                        $action->successNotificationTitle('Transfer success');

                        return $record;
                    }

                    $action->failureNotificationTitle('Transfer failed')->failure();

                    return $action->halt();
                }),
        ];
    }
}
