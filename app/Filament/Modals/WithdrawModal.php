<?php

namespace App\Filament\Modals;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Modals\Modal;
use Illuminate\Support\Facades\Auth;

class WithdrawModal extends Modal
{
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('amount')
                    ->label('Amount')
                    ->numeric()
                    ->required()
                    ->prefix('$')
                    ->minValue(1)
                    ->maxValue(Auth::user()->balance),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        // Add your withdrawal logic here
        $user = Auth::user();
        $user->balance -= $data['amount'];
        $user->save();

        $this->close();
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Withdrawal successful!',
        ]);
    }

    public static function getModalId(): string
    {
        return 'withdraw-modal';
    }
}
