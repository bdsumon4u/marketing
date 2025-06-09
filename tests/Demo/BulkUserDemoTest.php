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

        // Admin approves the deposit
        if ($deposit) {
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
        }

        // Simulate verification process using Livewire
        Livewire::actingAs($user)
            ->test(VerifyNowModal::class)
            ->set('data.package', $package)
            ->call('submit');

        // Assert the user has been properly set up
        expect($user->fresh())
            ->is_active->toBeTrue()
            ->total_deposit->toBe(round($amount, 2));
    }

    // Reset time travel
    Carbon::setTestNow();
});
