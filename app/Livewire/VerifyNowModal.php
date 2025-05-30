<?php

namespace App\Livewire;

use App\Models\Admin;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms\Components\Radio;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Livewire\Component;

class VerifyNowModal extends Component implements HasForms
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
                Radio::make('package')
                    ->label('Select Package')
                    ->options([
                        'with_product' => 'With Product (Tk. 1,000)',
                        'without_product' => 'Without Product (Tk. 500)',
                    ])
                    ->required()
                    ->inline()
                    ->default('without_product'),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $data = $this->form->getState();
        $user = value(fn (): User => Filament::auth()->user());

        $amount = match ($data['package']) {
            'with_product' => 1000,
            'without_product' => 500,
        };

        if ($user->balanceFloat < $amount) {
            Notification::make()
                ->danger()
                ->title('Insufficient balance')
                ->body('Please add funds to your account first.')
                ->send();

            return;
        }

        $user->update(['is_active' => true]);
        $user->withdrawFloat($amount, [
            'action' => 'verification',
            'message' => 'Account Verification - '.($data['package'] === 'with_product' ? 'With Product' : 'Without Product'),
        ]);

        $this->dispatch('refresh-balance');

        Notification::make()
            ->success()
            ->title('Account verified successfully!')
            ->body('Your account has been verified. You can now use all features.')
            ->sendToDatabase($user)
            ->send();

        Notification::make()
            ->info()
            ->title('New account verification')
            ->body('@'.$user->username.' has verified their account with '.($data['package'] === 'with_product' ? 'With Product' : 'Without Product').' package.')
            ->sendToDatabase(Admin::all());

        $this->dispatch('close-modal', id: 'verify-now-modal');
    }

    public function render()
    {
        return view('livewire.verify-now-modal');
    }
}
