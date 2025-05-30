<?php

namespace App\Jobs;

use App\Enums\CompanyWalletType;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class ProcessMLMIncentives implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected User $newUser,
        protected string $package,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->newUser->balanceFloat < Arr::get(config('mlm.registration_fee'), $this->package)) {
            info('Insufficient balance');
            return;
        }
        info('Building referral chain');
        $referralChain = $this->buildReferralChain($this->newUser);
        info('Referral chain built');
        $registrationFee = config('mlm.registration_fee.without_product');
        $distributedAmount = 0;
        foreach ($referralChain as $index => $referrer) {
            $level = $index + 1;
            $incentiveAmount = $referrer->getReferralIncentive($level);
            $distributedAmount += $incentiveAmount;
            $this->newUser->transferFloat($referrer->getOrCreateWallet('earning'), $incentiveAmount, [
                'message' => "Referral incentive for level {$level}",
                'meta' => [
                    'level' => $level,
                    'type' => 'referral',
                    'amount' => $incentiveAmount,
                    'referred_user_id' => $this->newUser->id,
                ],
            ]);
        }
        info('Transferring registration fee');
        $companyWallet = Wallet::company()->getWallet(CompanyWalletType::COMPANY->value);
        $this->newUser->transferFloat($companyWallet, $registrationFee, [
            'message' => 'Registration fee',
            'user_id' => $this->newUser->id,
        ]);
        info('Registration fee debited');
        foreach (Wallet::company()->wallets()->with('holder')->oldest('id')->get() as $wallet) {
            if ($wallet->slug === CompanyWalletType::COMPANY->value) {
                continue;
            }
            $amount = CompanyWalletType::from($wallet->slug)->getIncentive();
            $companyWallet->transferFloat($wallet, $amount, [
                'message' => 'Registration fee distribution',
                'user_id' => $this->newUser->id,
            ]);
            $distributedAmount += $amount;
        }
        info('Company wallets distributed');
        if ($this->package === 'with_product') {
            $productFund = config('mlm.registration_fee.with_product') - $registrationFee;
            $this->newUser->transferFloat($this->newUser->getOrCreateWallet('product'), $productFund, [
                'message' => 'Product fund',
                'user_id' => $this->newUser->id,
            ]);
        }
        info('Product fund distributed');
        $this->newUser->update(['is_active' => true]);
    }

    protected function buildReferralChain(?User $newUser, int $depth = 10): array
    {
        if (! $newUser->referrer) {
            return [];
        }

        $chain = [$newUser->referrer];
        $currentUser = $newUser->referrer;

        for ($i = 1; $i < $depth; $i++) {
            if (! $currentUser->referrer) {
                break;
            }
            $chain[] = $currentUser->referrer->loadCount('referrals');
            $currentUser = $currentUser->referrer;
        }

        return $chain;
    }
}
