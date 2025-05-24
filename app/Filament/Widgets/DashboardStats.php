<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;

class DashboardStats extends Widget
{
    protected static string $view = 'filament.widgets.dashboard-stats';

    protected int | string | array $columnSpan = 'full';

    public function getReferralLink(): string
    {
        return Filament::getPanel()->getRegistrationUrl([
            'ref' => Filament::auth()->user()->referral_code,
        ]);
    }

    public function notifyCopied(): void
    {
        Notification::make()
            ->success()
            ->title('Referral link copied!')
            ->send();
    }
}
