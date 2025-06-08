<?php

namespace App\Console\Commands;

use App\Enums\UserRank;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Console\Command;

class DistributeSpecialFunds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:distribute-special-funds';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Distribute special funds to users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::query()
            ->where('rank', '>=', UserRank::_M->value)
            ->where('rank_updated_at', '>=', now()->subMonth()->startOfMonth())
            ->where('rank_updated_at', '<=', now()->subMonth()->endOfMonth())
            ->get()
            ->groupBy('rank');

        $this->info('Distributing special funds to users...');
        $this->info('Users: ' . $users->count());

        foreach ($users as $rank => $users) {
            $rank = UserRank::from($rank);
            $this->info('Rank: ' . $rank->name);
            $this->info('Users: ' . $users->count());
            $wallet = Wallet::company()->getWallet($rank->getWalletSlug());
            $amount = $wallet->balanceFloat / count($users);
            $this->info('Amount: ' . $amount);
            foreach ($users as $user) {
                $wallet->transferFloat($user->getOrCreateWallet('earning'), $amount, [
                    'type' => 'special',
                    'description' => 'Rank fund distribution',
                    'reference' => $rank->getWalletSlug(),
                ]);
            }
            $this->info('--------------------------------');
        }
    }
}
