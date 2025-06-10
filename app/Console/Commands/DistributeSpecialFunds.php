<?php

namespace App\Console\Commands;

use App\Enums\UserRank;
use App\Jobs\DistributeSpecialFunds as DistributeSpecialFundsJob;
use App\Models\User;
use Illuminate\Console\Command;

class DistributeSpecialFunds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:distribute-special-funds {month? : The month to distribute funds for (format: YYYY-MM)}';

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
        $month = $this->argument('month') ?? now()->subMonth()->format('Y-m');

        $usersByRank = User::query()
            ->where('rank', '>=', UserRank::_M->value)
            ->where('rank_updated_at', '>=', now()->parse($month)->startOfMonth())
            ->where('rank_updated_at', '<=', now()->parse($month)->endOfMonth())
            ->get()
            ->groupBy('rank');

        $this->info('Found '.$usersByRank->count().' ranks to distribute funds for '.$month);

        foreach ($usersByRank as $rank => $users) {
            $rank = UserRank::from($rank);
            $this->info($users->count().' users has rank: '.$rank->name);
        }

        if ($this->confirm('Do you want to proceed with distribution?')) {
            foreach ($usersByRank as $rank => $users) {
                $rank = UserRank::from($rank);
                DistributeSpecialFundsJob::dispatch($users, $rank, $month);
                $this->info('Distribution job has been queued for rank '.$rank->name.' for '.$month);
            }
        }
    }
}
