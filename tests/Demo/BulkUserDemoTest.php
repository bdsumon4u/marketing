<?php

use App\Livewire\AddFundModal;
use App\Livewire\VerifyNowModal;
use App\Models\Admin;
use App\Models\User;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Support\Number;
use Livewire\Livewire;

test('can generate 1000 random users with deposits and verifications', function () {
    // Create an admin user for deposit approval
    $admin = Admin::query()->firstOrCreate([
        'email' => 'admin@example.com',
    ], [
        'name' => 'Mr. Admin',
        'password' => bcrypt('password'),
    ]);

    // Start from 90 days ago
    $startDate = Carbon::now()->subDays(90);

    // Calculate time interval between users (in minutes)
    // 90 days * 24 hours * 60 minutes = 129600 minutes
    // We want to distribute 1000 users over this period
    // 129600 / 1000 = 129.6 minutes between each user
    $minutesBetweenUsers = 130;

    // Initialize counters
    $confirmedCount = 0;
    $pendingCount = 0;
    $rejectedCount = 0;
    $confirmedAmount = 0;
    $pendingAmount = 0;
    $rejectedAmount = 0;
    $withProductCount = 0;
    $withoutProductCount = 0;

    // Generate 1000 users
    for ($i = 0; $i < 1000; $i++) {
        // Travel to a specific time
        $currentDate = $startDate->copy()->addMinutes($i * $minutesBetweenUsers);
        Carbon::setTestNow($currentDate);

        // Create a random user
        $user = User::factory()->create([
            'email' => "user{$i}@example.com",
            'created_at' => $currentDate,
        ])->fresh();

        // Randomly decide if user will verify with or without product
        $withProduct = rand(0, 1) === 1;
        $package = $withProduct ? 'with_product' : 'without_product';
        $amount = $withProduct ? 1000 : 500;

        if ($withProduct) {
            $withProductCount++;
        } else {
            $withoutProductCount++;
        }

        // Simulate deposit process using Livewire
        Livewire::actingAs($user)
            ->test(AddFundModal::class)
            ->set('data.amount', $amount)
            ->set('data.transaction_id', 'TRX'.str_pad($i, 6, '0', STR_PAD_LEFT))
            ->call('submit');

        // Get the latest deposit transaction
        $deposit = $user->transactions()
            ->where('type', 'deposit')
            ->where('meta->action', 'deposit')
            ->latest()
            ->first();

        if ($deposit) {
            // Randomly decide deposit status
            // 85% confirmed, 10% pending, 5% rejected
            $status = rand(1, 100);

            if ($status <= 85) {
                // Confirm deposit (85% of cases)
                $user->confirm($deposit);
                $user->decrement('pending_deposit', $deposit->amount);
                $user->increment('total_deposit', $deposit->amount);

                // Send notifications
                Notification::make()
                    ->title('Deposit confirmed')
                    ->body('The deposit has been confirmed.')
                    ->success()
                    ->sendToDatabase($admin);

                Notification::make()
                    ->title('Deposit confirmed')
                    ->body('The deposit of '.Number::currency($deposit->amountFloat).' has been confirmed.')
                    ->success()
                    ->sendToDatabase($user);

                // Only verify account if deposit is confirmed
                Livewire::actingAs($user)
                    ->test(VerifyNowModal::class)
                    ->set('data.package', $package)
                    ->call('submit');

                // Assert the user has been properly set up
                expect($user->fresh())
                    ->is_active->toBeTrue()
                    ->total_deposit->toBe(round($amount, 2));

                $confirmedCount++;
                $confirmedAmount += $amount;
            } elseif ($status <= 95) {
                // Leave deposit pending (10% of cases)
                $pendingCount++;
                $pendingAmount += $amount;

                // Assert the user has pending deposit
                expect($user->fresh())
                    ->is_active->toBeFalse()
                    ->pending_deposit->toBe(round($amount, 2));
            } else {
                // Reject deposit (5% of cases)
                $user->decrement('pending_deposit', $deposit->amount);
                $user->increment('rejected_deposit', $deposit->amount);

                Notification::make()
                    ->title('Deposit rejected')
                    ->body('The deposit has been rejected.')
                    ->warning()
                    ->sendToDatabase($admin);

                Notification::make()
                    ->title('Deposit rejected')
                    ->body('The deposit #'.($deposit->meta['transaction_id'] ?? '').' of '.Number::currency($deposit->amountFloat).' has been rejected.')
                    ->danger()
                    ->sendToDatabase($user);

                $deposit->delete();

                // Assert the user has rejected deposit
                expect($user->fresh())
                    ->is_active->toBeFalse()
                    ->rejected_deposit->toBe(round($amount, 2));

                $rejectedCount++;
                $rejectedAmount += $amount;
            }
        }
    }

    // Print statistics
    echo "\n\n=== Demo Data Generation Statistics ===\n";
    echo "Total Users Generated: 1000\n\n";

    echo "Deposit Status Distribution:\n";
    echo "----------------------------\n";
    echo "Confirmed Deposits: {$confirmedCount} users (".Number::currency($confirmedAmount).")\n";
    echo "Pending Deposits: {$pendingCount} users (".Number::currency($pendingAmount).")\n";
    echo "Rejected Deposits: {$rejectedCount} users (".Number::currency($rejectedAmount).")\n\n";

    echo "Verification Package Distribution:\n";
    echo "--------------------------------\n";
    echo "With Product (1000 BDT): {$withProductCount} users\n";
    echo "Without Product (500 BDT): {$withoutProductCount} users\n";
    echo "--------------------------------\n";
    echo 'Total Amount: '.Number::currency($confirmedAmount + $pendingAmount + $rejectedAmount)."\n\n";

    // Reset time travel
    Carbon::setTestNow();
});
