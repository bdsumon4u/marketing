<?php

namespace Database\Seeders;

use App\Filament\Common\Resources\WithdrawResource\Pages\ListWithdraws;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Notification;

class WithdrawSeeder extends Seeder
{
    protected Carbon $startDate;

    protected int $totalWithdraws = 200;

    protected int $days = 30;

    protected int $minutesBetweenWithdraws;

    protected array $stats = [
        'confirmed' => ['count' => 0, 'amount' => 0],
        'pending' => ['count' => 0, 'amount' => 0],
        'rejected' => ['count' => 0, 'amount' => 0],
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
        $this->minutesBetweenWithdraws = ceil($this->days * 24 * 60 / $this->totalWithdraws);

        // Get all active users with sufficient balance
        $users = User::where('is_active', true)
            ->whereHas('wallets', function ($query) {
                $query->where('slug', 'earning')
                    ->where('balance', '>', 500 * 100);
            })
            ->get();

        if ($users->isEmpty()) {
            echo "\nNo users found with sufficient balance for withdrawals\n";

            return;
        }

        for ($i = 0; $i < $this->totalWithdraws; $i++) {
            $currentDate = $this->startDate->copy()->addMinutes($i * $this->minutesBetweenWithdraws);
            Carbon::setTestNow($currentDate);

            $user = $users->random();
            $balance = (int) $user->getOrCreateWallet('earning')->balanceFloat;
            $amount = mt_rand(500, max(500, min(2000, $balance)));
            $withdraw = (new ListWithdraws)->processWithdraw([
                'amount' => $amount,
                'bkash_number' => $user->phone,
            ], null, $user);

            if ($withdraw) {
                $this->processWithdraw($withdraw, $i);
            }
        }

        echo "\nGenerated {$this->totalWithdraws} withdrawals with proper time distribution\n";

        // Print statistics
        echo "\nWithdrawal Status Distribution:\n";
        echo "----------------------------\n";
        echo "Confirmed Withdrawals: {$this->stats['confirmed']['count']} withdrawals\n";
        echo "Pending Withdrawals: {$this->stats['pending']['count']} withdrawals\n";
        echo "Rejected Withdrawals: {$this->stats['rejected']['count']} withdrawals\n";
    }

    protected function processWithdraw($withdraw, int $index): void
    {
        $status = rand(1, 100);

        if ($status <= 90) {
            $this->confirmWithdraw($withdraw, $index);
        } elseif ($status <= 97) {
            $this->leavePending($withdraw);
        } else {
            $this->rejectWithdraw($withdraw);
        }
    }

    protected function confirmWithdraw($withdraw, int $index): void
    {
        $user = value(fn (): User => $withdraw->payable);
        $wallet = $user->getOrCreateWallet('earning');
        $wallet->confirm($withdraw);
        $meta = $withdraw->meta;
        $meta['transaction_id'] = 'WTH'.str_pad($index, 6, '0', STR_PAD_LEFT);
        $withdraw->meta = $meta;
        $withdraw->save();
        $user->decrement('pending_withdraw', abs($withdraw->amount));
        $user->increment('total_withdraw', abs($withdraw->amount));

        $this->stats['confirmed']['count']++;
        $this->stats['confirmed']['amount'] += abs($withdraw->amountFloat);
    }

    protected function leavePending($withdraw): void
    {
        $this->stats['pending']['count']++;
        $this->stats['pending']['amount'] += abs($withdraw->amountFloat);
    }

    protected function rejectWithdraw($withdraw): void
    {
        $user = $withdraw->payable;
        $user->decrement('pending_withdraw', abs($withdraw->amount));
        $user->increment('rejected_withdraw', abs($withdraw->amount));

        $this->stats['rejected']['count']++;
        $this->stats['rejected']['amount'] += abs($withdraw->amountFloat);
    }
}
