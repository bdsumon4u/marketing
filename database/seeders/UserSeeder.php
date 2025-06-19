<?php

namespace Database\Seeders;

use App\Enums\UserRank;
use App\Livewire\AddFundModal;
use App\Livewire\VerifyNowModal;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;

class UserSeeder extends Seeder
{
    protected Carbon $startDate;

    protected int $totalUsers = 500;

    protected int $days = 60;

    protected int $minutesBetweenUsers;

    protected array $stats = [
        'confirmed' => ['count' => 0, 'amount' => 0],
        'pending' => ['count' => 0, 'amount' => 0],
        'rejected' => ['count' => 0, 'amount' => 0],
        'with_product' => 0,
        'without_product' => 0,
        'unverified' => 0,
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Fake notifications
        Notification::fake();

        // Set queue connection to sync to process jobs synchronously
        config(['queue.default' => 'sync']);

        $this->startDate = Carbon::now()->subDays($this->days);
        $this->minutesBetweenUsers = ceil($this->days * 24 * 60 / $this->totalUsers);

        for ($i = 0; $i < $this->totalUsers; $i++) {
            $currentDate = $this->startDate->copy()->addMinutes($i * $this->minutesBetweenUsers);
            Carbon::setTestNow($currentDate);

            $user = User::factory()->create([
                'email' => "user{$i}@example.com",
                'created_at' => $currentDate,
            ]);

            $this->createDeposit($user, $i);
            $this->verifyUser($user);
        }

        echo "\nGenerated {$this->totalUsers} users with proper time distribution\n";

        // Print statistics
        echo "\nDeposit Status Distribution:\n";
        echo "----------------------------\n";
        echo "Confirmed Deposits: {$this->stats['confirmed']['count']} users\n";
        echo "Pending Deposits: {$this->stats['pending']['count']} users\n";
        echo "Rejected Deposits: {$this->stats['rejected']['count']} users\n";

        // Print statistics
        echo "\nVerification Package Distribution:\n";
        echo "--------------------------------\n";
        echo "With Product (1000 BDT): {$this->stats['with_product']} users\n";
        echo "Without Product (500 BDT): {$this->stats['without_product']} users\n";
        echo "Unverified: {$this->stats['unverified']} users\n";
    }

    protected function createDeposit(User $user, int $index): void
    {
        $amount = mt_rand(500, 2500);

        // Simulate deposit process using Livewire
        $deposit = (new AddFundModal)->processDeposit($user, [
            'amount' => $amount,
            'transaction_id' => 'TRX'.str_pad($index, 6, '0', STR_PAD_LEFT),
        ]);

        $this->processDeposit($deposit, $amount);
    }

    protected function processDeposit($deposit, float $amount): void
    {
        $status = rand(1, 100);

        if ($status <= 90) {
            $this->confirmDeposit($deposit, $amount);
        } elseif ($status <= 97) {
            $this->leavePending($deposit, $amount);
        } else {
            $this->rejectDeposit($deposit, $amount);
        }
    }

    protected function confirmDeposit($deposit, float $amount): void
    {
        $user = $deposit->payable;
        $user->confirm($deposit);
        $user->decrement('pending_deposit', $deposit->amount);
        $user->increment('total_deposit', $deposit->amount);

        $this->stats['confirmed']['count']++;
        $this->stats['confirmed']['amount'] += $amount;
    }

    protected function leavePending($deposit, float $amount): void
    {
        $this->stats['pending']['count']++;
        $this->stats['pending']['amount'] += $amount;
    }

    protected function rejectDeposit($deposit, float $amount): void
    {
        $user = $deposit->payable;
        $user->decrement('pending_deposit', $deposit->amount);
        $user->increment('rejected_deposit', $deposit->amount);

        $this->stats['rejected']['count']++;
        $this->stats['rejected']['amount'] += $amount;
    }

    protected function verifyUser(User $user): void
    {
        // Leave 1% unverified
        if (rand(1, 100) <= 1) {
            $this->stats['unverified']++;

            return;
        }

        // Check if user has at least 1000 balance
        if ($user->balanceFloat >= 1000) {
            // 50% chance of verifying with product
            $withProduct = rand(0, 1) === 1;
        } else {
            // Users with less than 1000 balance always verify without product
            $withProduct = false;
        }

        $package = $withProduct ? 'with_product' : 'without_product';

        // Process jobs synchronously to maintain time sequence
        $verifyModal = new VerifyNowModal;
        $verifyModal->verifyUser($user, ['package' => $package]);

        $this->stats[$package]++;
    }

    protected function buildReferralChain(?User $newUser, int $depth = 10): array
    {
        $chain = [];
        $currentUser = $newUser;

        while ($currentUser && $currentUser->referrer && count($chain) < $depth) {
            $chain[] = $currentUser->referrer;
            $currentUser = $currentUser->referrer;
        }

        return $chain;
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
