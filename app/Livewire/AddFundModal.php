<?php

namespace App\Livewire;

use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
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
        $user = value(fn (): User => Filament::auth()->user());
        $user->depositFloat($data['amount']);

        $this->dispatch('refresh-balance');

        $this->form->fill();

        Notification::make()
            ->title('Funds added successfully!')
            ->success()
            ->send();

        $this->dispatch('close-modal', id: 'add-fund-modal');
    }

    public function render()
    {
        return view('livewire.add-fund-modal');
    }
}
