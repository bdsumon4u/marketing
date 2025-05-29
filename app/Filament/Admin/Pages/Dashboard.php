<?php

namespace App\Filament\Admin\Pages;

use App\Models\User;
use App\Models\Wallet;
use Filament\Pages\Dashboard as DashboardPage;
use Livewire\Attributes\Computed;

class Dashboard extends DashboardPage
{
    protected static string $view = 'filament.admin.pages.dashboard';

    #[Computed(true)]
    public function totalDeposit(): float
    {
        return round(User::sum('total_deposit') / 100, 2);
    }

    #[Computed(true)]
    public function totalWithdraw(): float
    {
        return round(User::sum('total_withdraw') / 100, 2);
    }

    #[Computed(true)]
    public function companyBalance(): float
    {
        // return Wallet::company()->balanceFloat;

        return Wallet::company()->getWallet('company')->balanceFloat;
    }

    #[Computed(true)]
    public function pendingDeposit(): float
    {
        return round(User::sum('pending_deposit') / 100, 2);
    }

    #[Computed(true)]
    public function rejectedDeposit(): float
    {
        return round(User::sum('rejected_deposit') / 100, 2);
    }

    #[Computed(true)]
    public function pendingWithdraw(): float
    {
        return round(User::sum('pending_withdraw') / 100, 2);
    }

    #[Computed(true)]
    public function rejectedWithdraw(): float
    {
        return round(User::sum('rejected_withdraw') / 100, 2);
    }
}
