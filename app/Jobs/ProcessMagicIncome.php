<?php

namespace App\Jobs;

use App\Enums\CompanyWalletType;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Queue\Queueable;

class ProcessMagicIncome implements ShouldQueue
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
        $link = $this->buildMagicLink($this->user);
        $companyWallet = Wallet::company()->getWallet(CompanyWalletType::COMPANY->value);
        foreach ($link as $user) {
            $companyWallet->transferFloat($user->getOrCreateWallet('earning'), $user->getMagicIncome(), [
                'action' => 'income',
                'message' => 'Magic income',
                'user_id' => $this->user->id,
            ]);
            $user->increment('magic_income', $user->getMagicIncome() * 100);
        }
    }

    protected function buildMagicLink(User $user, $limit = 10): Collection
    {
        $link = [];
        $currentId = $user->id - 1000;
        for ($i = 0; $i < $limit; $i++) {
            $currentId = (int) ($currentId / 2);
            $link[] = $currentId;
        }
        $link = array_map(fn ($id) => $id + 1000, $link);

        return User::whereIn('id', $link)->where('is_active', true)->get();
    }
}
