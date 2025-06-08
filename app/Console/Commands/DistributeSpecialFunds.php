<?php

namespace App\Console\Commands;

use App\Enums\UserRank;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Console\Command;
use Illuminate\Support\Number;

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

        $this->info('Distributing special funds to ' . $users->count() . ' users...');

        foreach ($users as $rank => $users) {
            $rank = UserRank::from($rank);
            $this->info('Users: '.$users->count().' has rank: '.$rank->name);
            $wallet = Wallet::company()->getWallet($rank->getWalletSlug());
            $amountPerUser = $wallet->balanceFloat / count($users);
            $this->info('Amount per user: '.Number::currency($amountPerUser));
            foreach ($users as $user) {
                $wallet->transferFloat($user->getOrCreateWallet('earning'), $amountPerUser, [
                    'action' => 'income',
                    'type' => 'special',
                    'description' => 'Rank fund distribution',
                    'reference' => $rank->getWalletSlug(),
                ]);
                $this->info('Transferred '.Number::currency($amountPerUser).' to '.$user->username);
            }
            $this->info('--------------------------------');
        }
    }
}
