<?php

namespace App\Livewire;

use App\Jobs\ProcessMLMIncentives;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms\Components\Radio;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Support\Arr;
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
                    ->columns(2)
                    ->required(),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $data = $this->form->getState();
        $user = value(fn (): User => Filament::auth()->user());

        if ($user->is_active) {
            Notification::make()
                ->info()
                ->title('Account already verified!')
                ->send();

            return;
        }

        $amount = Arr::get(config('mlm.registration_fee'), $data['package']);

        if ($user->balanceFloat < $amount) {
            Notification::make()
                ->danger()
                ->title('Insufficient balance!')
                ->body('You need at least '.$amount.' BDT to verify your account.')
                ->send();

            return;
        }

        ProcessMLMIncentives::dispatch($user, $data['package']);

        Notification::make()
            ->success()
            ->title('Account is being verified...')
            ->send();

        $this->dispatch('close-modal', id: 'verify-now-modal');
    }

    public function render()
    {
        return view('livewire.verify-now-modal');
    }
}
