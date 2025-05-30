<?php

namespace App\Jobs;

use App\Enums\CompanyWalletType;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessReferralIncentive implements ShouldQueue
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
        $referralChain = $this->buildReferralChain($this->user);
        $companyWallet = Wallet::company()->getWallet(CompanyWalletType::COMPANY->value);
        $distributedAmount = 0;
        foreach ($referralChain as $index => $referrer) {
            $level = $index + 1;
            $incentiveAmount = $referrer->getReferralIncentive($level);
            $distributedAmount += $incentiveAmount;
            $companyWallet->transferFloat($referrer->getOrCreateWallet('earning'), $incentiveAmount, [
                'message' => "Referral incentive for level {$level}",
                'meta' => [
                    'level' => $level,
                    'type' => 'referral',
                    'amount' => $incentiveAmount,
                    'referred_user_id' => $this->user->id,
                ],
            ]);
            if ($level === 1) {
                $referrer->increment('referral_income', $incentiveAmount * 100);
            } else {
                $referrer->increment('generation_income', $incentiveAmount * 100);
            }
        }
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
