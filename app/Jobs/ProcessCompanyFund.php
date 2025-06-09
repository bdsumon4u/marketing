<?php

namespace App\Jobs;

use App\Enums\CompanyWalletType;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessCompanyFund implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected User $user,
        protected string $package,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        info('Processing company fund', ['user' => $this->user->id, 'package' => $this->package]);
        $companyWallet = Wallet::company()->getWallet(CompanyWalletType::COMPANY->value);
        foreach (Wallet::company()->wallets()->with('holder')->oldest('id')->get() as $wallet) {
            if ($wallet->slug === CompanyWalletType::COMPANY->value) {
                continue;
            }
            $amount = CompanyWalletType::from($wallet->slug)->getIncentive();
            $companyWallet->transferFloat($wallet, $amount, [
                'action' => 'income',
                'message' => 'Registration fee distribution',
                'user_id' => $this->user->id,
            ]);
        }
    }
}
