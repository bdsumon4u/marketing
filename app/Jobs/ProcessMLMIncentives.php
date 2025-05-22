<?php

namespace App\Jobs;

use App\Models\User;
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
        protected User $firstReferrer
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $registrationFee = config('mlm.registration_fee');
        $referralChain = $this->buildReferralChain($this->firstReferrer);

        foreach ($referralChain as $index => $referrer) {
            $level = $index + 1;
            $incentivePercentage = $referrer->getReferralIncentive($level);
            $incentiveAmount = ($registrationFee * $incentivePercentage) / 100;

            // Credit the referrer's wallet
            $referrer->deposit($incentiveAmount, [
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
    }

    protected function buildReferralChain(User $firstReferrer, int $depth = 10): array
    {
        $chain = [$firstReferrer];
        $currentUser = $firstReferrer;

        for ($i = 1; $i < $depth; $i++) {
            if (! $currentUser->referrer) {
                break;
            }
            $chain[] = $currentUser->referrer;
            $currentUser = $currentUser->referrer;
        }

        return $chain;
    }
}
