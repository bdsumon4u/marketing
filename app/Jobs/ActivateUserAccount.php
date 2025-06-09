<?php

namespace App\Jobs;

use App\Enums\UserRank;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ActivateUserAccount implements ShouldQueue
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
        info('Activating user account', ['user' => $this->user->id, 'package' => $this->package]);
        $this->user->update(['is_active' => true, 'with_product' => $this->package === 'with_product']);

        $this->calculateAndUpdateRanks($this->user->referrer);
    }

    protected function calculateAndUpdateRanks(?User $user): void
    {
        if (! $user || $user->rank === UserRank::getMaximumRank()) {
            return;
        }

        $maxRank = $user->referrals()
            ->where('is_active', true)
            ->get()
            ->groupBy('rank')
            ->map(fn ($group) => $group->count())
            ->filter(fn ($count) => $count >= config('mlm.rank_threshold'))
            ->keys()
            ->max();

        if (is_null($maxRank)) {
            return;
        }

        $newRank = $maxRank + 1;

        if ($user->rank->value !== $newRank) {
            $user->update(['rank' => $newRank, 'rank_updated_at' => now()]);
            $this->calculateAndUpdateRanks($user->referrer);
        }
    }
}
