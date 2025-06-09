<?php

namespace App\Livewire;

use App\Jobs\ActivateUserAccount;
use App\Jobs\ProcessCompanyFund;
use App\Jobs\ProcessMagicIncome;
use App\Jobs\ProcessReferralIncentive;
use App\Jobs\ProcessRegistrationFee;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms\Components\Radio;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Bus;
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

        $this->verifyUser($user, $data);

        $this->dispatch('close-modal', id: 'verify-now-modal');
    }

    public function verifyUser(User $user, array $data): void
    {
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

        Bus::chain([
            new ProcessRegistrationFee($user, $data['package']),
            new ProcessCompanyFund($user, $data['package']),
            new ProcessReferralIncentive($user, $data['package']),
            new ProcessMagicIncome($user, $data['package']),
            new ActivateUserAccount($user, $data['package']),
        ])->dispatch();

        Notification::make()
            ->success()
            ->title('Account is being verified...')
            ->send();
    }

    public function render()
    {
        return view('livewire.verify-now-modal');
    }
}
