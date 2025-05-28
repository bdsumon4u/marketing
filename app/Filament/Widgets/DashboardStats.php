<?php

namespace App\Filament\Widgets;

use App\Jobs\ProcessMLMIncentives;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;

class DashboardStats extends Widget
{
    protected static string $view = 'filament.widgets.dashboard-stats';

    protected int | string | array $columnSpan = 'full';

    protected function getListeners(): array
    {
        return [
            'refresh-balance' => '$refresh',
        ];
    }

    public function getReferralLink(): string
    {
        return Filament::getPanel()->getRegistrationUrl([
            'ref' => Filament::auth()->user()->username,
        ]);
    }

    public function notifyCopied(): void
    {
        Notification::make()
            ->success()
            ->title('Referral link copied!')
            ->send();
    }

    public function verifyAccount(): void
    {
        $registrationFee = config('mlm.registration_fee');
        $user = value(fn (): User => Filament::auth()->user());
        if ($user->is_active) {
            Notification::make()
                ->info()
                ->title('Account already verified!')
                ->send();
            return;
        }
        if ($user->balanceFloat < $registrationFee) {
            Notification::make()
                ->danger()
                ->title('Insufficient balance!')
                ->body('You need at least ' . $registrationFee . ' BDT to verify your account.')
                ->send();
            return;
        }
        ProcessMLMIncentives::dispatch($user);
        Notification::make()
            ->success()
            ->title('Account is being verified...')
            ->send();
    }
}
