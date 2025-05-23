<?php

namespace App\Livewire;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Livewire\Component;

class WithdrawModal extends Component implements HasForms
{
    use InteractsWithForms;

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
                    ->maxValue(auth()->user()->balance),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        // Add your withdrawal logic here
        $user = auth()->user();
        $user->balance -= $data['amount'];
        $user->save();

        $this->dispatch('close-modal', id: 'withdraw-modal');
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Withdrawal successful!',
        ]);
    }

    public function render()
    {
        return view('livewire.withdraw-modal');
    }
}
