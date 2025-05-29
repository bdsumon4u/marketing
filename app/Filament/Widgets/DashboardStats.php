<?php

namespace App\Filament\Widgets;

use App\Jobs\ProcessMLMIncentives;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Widgets\Concerns\CanPoll;
use Filament\Widgets\Widget;

class DashboardStats extends Widget
{
    use CanPoll;

    protected static string $view = 'filament.widgets.dashboard-stats';

    protected int|string|array $columnSpan = 'full';

    protected function getListeners(): array
    {
        return [
            'refresh-balance' => '$refresh',
        ];
    }

    protected function user(): User
    {
        return Filament::auth()->user();
    }

    public function verifyAccount(): void
    {
        $registrationFee = config('mlm.registration_fee');
        if ($this->user()->is_active) {
            Notification::make()
                ->info()
                ->title('Account already verified!')
                ->send();

            return;
        }
        if ($this->user()->balanceFloat < $registrationFee) {
            Notification::make()
                ->danger()
                ->title('Insufficient balance!')
                ->body('You need at least '.$registrationFee.' BDT to verify your account.')
                ->send();

            return;
        }
        ProcessMLMIncentives::dispatch($this->user());
        Notification::make()
            ->success()
            ->title('Account is being verified...')
            ->send();
    }
}
