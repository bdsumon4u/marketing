<?php

namespace Tests\Demo;

use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Number;
use Livewire\Livewire;

class DepositProcessingTest extends TestCase
{
    protected array $stats = [
        'confirmed' => ['count' => 0, 'amount' => 0],
        'pending' => ['count' => 0, 'amount' => 0],
        'rejected' => ['count' => 0, 'amount' => 0],
    ];

    public function test_can_process_deposits_with_correct_distribution()
    {
        $users = User::with('wallet')->get();

        foreach ($users as $index => $user) {
            Carbon::setTestNow($user->created_at); // Time-travel to user creation time
            $this->createDeposit($user, $index);
        }

        // Assert deposit distribution
        $totalDeposits = $this->stats['confirmed']['count'] + $this->stats['pending']['count'] + $this->stats['rejected']['count'];
        expect($totalDeposits)->toBe($users->count());

        // Print statistics
        echo "\nDeposit Status Distribution:\n";
        echo "----------------------------\n";
        echo "Confirmed Deposits: {$this->stats['confirmed']['count']} users\n";
        echo "Pending Deposits: {$this->stats['pending']['count']} users\n";
        echo "Rejected Deposits: {$this->stats['rejected']['count']} users\n";
    }

    protected function createDeposit(User $user, int $index): void
    {
        $amount = mt_rand(500, 2500);

        // Simulate deposit process using Livewire
        Livewire::actingAs($user)
            ->test(\App\Livewire\AddFundModal::class)
            ->set('data.amount', $amount)
            ->set('data.transaction_id', 'TRX'.str_pad($index, 6, '0', STR_PAD_LEFT))
            ->call('submit');

        // Get the latest deposit transaction
        $deposit = $user->transactions()
            ->where('type', 'deposit')
            ->where('meta->action', 'deposit')
            ->latest()
            ->first();

        expect($deposit)->not->toBeNull();
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

        // send notification to user
        // Notification::make()
        //     ->title('Deposit confirmed')
        //     ->body('The deposit of '.Number::currency($deposit->amountFloat).' has been confirmed.')
        //     ->success()
        //     ->sendToDatabase($user);

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

        // Notification::make()
        //     ->title('Deposit rejected')
        //     ->body('The deposit #'.($deposit->meta['transaction_id'] ?? '').' of '.Number::currency($deposit->amountFloat).' has been rejected.')
        //     ->danger()
        //     ->sendToDatabase($user);

        $this->stats['rejected']['count']++;
        $this->stats['rejected']['amount'] += $amount;
    }
}
