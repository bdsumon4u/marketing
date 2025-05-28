<?php

namespace App\Livewire;

use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Support\Number;
use Livewire\Component;

use function Laravel\Prompts\confirm;

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
        $user = value(fn (): User => Filament::auth()->user());

        if ($user->hasPendingDeposit($data['amount'], $minutes = 10)) {
            Notification::make()
                ->danger()
                ->title('Duplicate deposit request')
                ->body('Please wait at least ' . $minutes . ' minutes before making another deposit request for the same amount.')
                ->send();
            return;
        }

        $user->depositFloat($data['amount'], confirmed: false);
        $user->increment('pending_deposit', $data['amount']);
        $this->dispatch('refresh-balance');

        $this->form->fill();

        Notification::make()
            ->success()
            ->title('Fund deposit is pending...')
            ->body(Number::currency($data['amount']) . ' BDT is being added to your account. Please wait for confirmation...')
            ->sendToDatabase($user)
            ->send();

        $this->dispatch('close-modal', id: 'add-fund-modal');
    }

    public function render()
    {
        return view('livewire.add-fund-modal');
    }
}
