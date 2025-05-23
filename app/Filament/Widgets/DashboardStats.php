<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class DashboardStats extends Widget
{
    protected static string $view = 'filament.widgets.dashboard-stats';

    protected int | string | array $columnSpan = 'full';

    public function getUser()
    {
        return auth()->user();
    }

    public function getReferralLink(): string
    {
        $user = $this->getUser();

        return url('/register?ref='.($user->referral_code ?? ''));
    }
}
