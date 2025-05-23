<?php

namespace App\Livewire;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Livewire\Component;

class AddFundModal extends Component implements HasForms
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
                    ->minValue(1),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        // Add your fund addition logic here
        $user = auth()->user();
        $user->balance += $data['amount'];
        $user->save();

        $this->dispatch('close-modal', id: 'add-fund-modal');
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Funds added successfully!',
        ]);
    }

    public function render()
    {
        return view('livewire.add-fund-modal');
    }
}
