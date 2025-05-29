<?php

namespace App\Filament\Admin\Pages;

use App\Models\User;
use App\Models\Wallet;
use Filament\Pages\Page;

use Filament\Pages\Dashboard as DashboardPage;
use Livewire\Attributes\Computed;

class Dashboard extends DashboardPage
{
    protected static string $view = 'filament.admin.pages.dashboard';

    #[Computed(true)]
    public function totalDeposit(): float
    {
        return User::sum('total_deposit');
    }

    #[Computed(true)]
    public function totalWithdraw(): float
    {
        return User::sum('total_withdraw');
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
        return User::sum('pending_deposit');
    }

    #[Computed(true)]
    public function rejectedDeposit(): float
    {
        return User::sum('rejected_deposit');
    }

    #[Computed(true)]
    public function pendingWithdraw(): float
    {
        return User::sum('pending_withdraw');
    }

    #[Computed(true)]
    public function rejectedWithdraw(): float
    {
        return User::sum('rejected_withdraw');
    }
}
