<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;

class ReferralLink extends Widget
{
    protected static string $view = 'filament.widgets.referral-link';

    protected int | string | array $columnSpan = 'full';

    protected function user(): User
    {
        return Filament::auth()->user();
    }

    public function getReferralLink(): string
    {
        return Filament::getPanel()->getRegistrationUrl([
            'ref' => $this->user()->username,
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
