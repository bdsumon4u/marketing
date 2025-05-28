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
use Illuminate\Support\Facades\Log;

class ProcessMLMIncentives implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected User $newUser,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $registrationFee = config('mlm.registration_fee');
        if ($this->newUser->balanceFloat < $registrationFee) {
            return;
        }
        $this->newUser->update(['is_active' => true]);
        $referralChain = $this->buildReferralChain($this->newUser);

        $distributedAmount = 0;
        foreach ($referralChain as $index => $referrer) {
            $level = $index + 1;
            $incentivePercentage = $referrer->getReferralIncentive($level);
            $incentiveAmount = ($registrationFee * $incentivePercentage) / 100;
            $distributedAmount += $incentiveAmount;
            // Credit the referrer's wallet
            // $referrer->deposit($incentiveAmount, [
            //     'description' => "Referral incentive for level {$level}",
            //     'meta' => [
            //         'level' => $level,
            //         'type' => 'referral',
            //         'amount' => $incentiveAmount,
            //         'percentage' => $incentivePercentage,
            //         'referred_user_id' => $this->newUser->id,
            //     ],
            // ]);
            $this->newUser->transferFloat($referrer->getOrCreateWallet('earning'), $incentiveAmount, [
                'description' => "Referral incentive for level {$level}",
                'meta' => [
                    'level' => $level,
                    'type' => 'referral',
                    'amount' => $incentiveAmount,
                    'percentage' => $incentivePercentage,
                    'referred_user_id' => $this->newUser->id,
                ],
            ]);

            Log::info('MLM incentive credited', [
                'referrer_id' => $referrer->id,
                'referred_user_id' => $this->newUser->id,
                'level' => $level,
                'percentage' => $incentivePercentage,
                'amount' => $incentiveAmount,
            ]);
        }

        // Distribute registration fee to company wallets (CompanyWalletType::COMPANY, etc.)
        foreach (Wallet::company()->wallets()->with('holder')->oldest('id')->get() as $wallet) {
            if ($wallet->slug === CompanyWalletType::COMPANY->value) {
                $amount = $registrationFee - $distributedAmount;
            } else {
                $percentageShare = $wallet->meta['percentage_share'] ?? 0;
                $amount = ($registrationFee * $percentageShare) / 100;
            }
            // $wallet->deposit($amount, [
            //     'description' => 'Registration fee distribution',
            //     'user_id' => $this->newUser->id,
            // ]);
            $this->newUser->transferFloat($wallet, $amount, [
                'description' => 'Registration fee distribution',
                'user_id' => $this->newUser->id,
            ]);
            $distributedAmount += $amount;
            Log::info('Company wallet credited', [
                'wallet_id' => $wallet->id,
                'wallet_slug' => $wallet->slug,
                'wallet_name' => $wallet->name,
                'amount' => $amount,
                'percentage_share' => $percentageShare,
                'description' => 'Registration fee distribution',
                'user_id' => $this->newUser->id,
            ]);
        }

        Log::info('New referral registered', [
            'referrer_id' => $this->newUser->referrer?->id,
            'referred_user_id' => $this->newUser->id,
            'referrer' => $this->newUser->referrer?->username,
            'registration_fee' => $registrationFee,
        ]);
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
