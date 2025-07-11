<?php

namespace App\Livewire;

use App\Models\Admin;
use App\Models\User;
use Bavix\Wallet\Models\Transaction;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Illuminate\Support\Number;
use Livewire\Component;

class AddFundModal extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('amount')
                    ->label('Amount')
                    ->numeric()
                    ->required()
                    ->minValue(1)
                    ->autofocus()
                    ->prefix(Number::defaultCurrency()),
                TextInput::make('transaction_id')
                    ->label('Transaction ID')
                    ->required(),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $data = $this->form->getState();
        $user = value(fn (): User => Filament::auth()->user());

        $this->processDeposit($user, $data);

        $this->dispatch('close-modal', id: 'add-fund-modal');
    }

    public function processDeposit(User $user, array $data): ?Transaction
    {
        if ($user->hasPendingDeposit($data['amount'], $minutes = 10)) {
            Notification::make()
                ->danger()
                ->title('Duplicate deposit request')
                ->body('Please wait at least '.$minutes.' minutes before making another deposit request for the same amount.')
                ->send();

            return null;
        }

        $deposit = $user->depositFloat($data['amount'], [
            'action' => 'deposit',
            'message' => 'Fund deposit',
            'reference' => $user->username,
            'transaction_id' => $data['transaction_id'],
        ], false);
        $user->increment('pending_deposit', $data['amount'] * 100);
        $this->dispatch('refresh-balance');

        $this->form->fill();

        Notification::make()
            ->success()
            ->title('Fund deposit is pending...')
            ->body(Number::currency($data['amount']).' BDT is being added to your account. Please wait for confirmation...')
            ->sendToDatabase($user)
            ->send();

        Notification::make()
            ->info()
            ->title('New deposit request')
            ->body('A new deposit request has been made by @'.$user->username.' for '.Number::currency($data['amount']).' BDT.')
            ->sendToDatabase(Admin::all());

        return $deposit;
    }

    public function render()
    {
        return view('livewire.add-fund-modal');
    }
}
