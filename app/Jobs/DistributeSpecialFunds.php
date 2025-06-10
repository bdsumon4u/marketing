<?php

namespace App\Jobs;

use App\Enums\UserRank;
use App\Models\Wallet;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class DistributeSpecialFunds implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected Collection $users,
        protected UserRank $rank,
        protected string $month,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $wallet = Wallet::company()->getWallet($this->rank->getWalletSlug());
        $amountPerUser = $wallet->balanceFloat / $this->users->count();

        foreach ($this->users as $user) {
            $wallet->transferFloat($user->getOrCreateWallet('earning'), $amountPerUser, [
                'action' => 'income',
                'type' => 'special',
                'message' => $this->rank->name . ' rank fund distribution',
            ]);
            $user->increment('rank_income', $amountPerUser * 100);
            $user->increment('total_income', $amountPerUser * 100);
        }
    }
}
