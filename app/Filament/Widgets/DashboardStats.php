<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Facades\Filament;
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
}
