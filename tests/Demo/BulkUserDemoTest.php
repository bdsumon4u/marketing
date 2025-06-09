<?php

use App\Models\Admin;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Illuminate\Support\Number;
use Tests\Demo\Generators\DepositGenerator;
use Tests\Demo\Generators\UserGenerator;
use Tests\Demo\Generators\VerificationGenerator;

test('can generate 1000 random users with deposits and verifications', function () {
    // Fake notifications
    NotificationFacade::fake();

    // Create an admin user for deposit approval
    $admin = Admin::query()->firstOrCreate([
        'email' => 'admin@example.com',
    ], [
        'name' => 'Mr. Admin',
        'password' => bcrypt('password'),
    ]);

    // Initialize generators
    $userGenerator = new UserGenerator(Carbon::now()->subDays(60), 1000, 60);
    $depositGenerator = new DepositGenerator($admin);
    $verificationGenerator = new VerificationGenerator;

    // Process users in smaller chunks
    $totalUsers = 1000;
    $chunkSize = 50;
    $chunks = ceil($totalUsers / $chunkSize);

    for ($chunk = 0; $chunk < $chunks; $chunk++) {
        $start = $chunk * $chunkSize;
        $end = min(($chunk + 1) * $chunkSize, $totalUsers);

        for ($i = $start; $i < $end; $i++) {
            // Generate user
            $user = $userGenerator->generate($i);

            // Create deposit
            $depositGenerator->create($user, $i);

            // Verify account if deposit was confirmed
            if ($user->fresh()->is_active) {
                $verificationGenerator->verify($user);
            }
        }

        echo "\nProcessed users {$start} to {$end} of {$totalUsers}\n";
    }

    // Get statistics
    $depositStats = $depositGenerator->getStats();
    $verificationStats = $verificationGenerator->getStats();

    // Print final statistics
    echo "\n\n=== Demo Data Generation Statistics ===\n";
    echo "Total Users Generated: {$totalUsers}\n\n";

    echo "Deposit Status Distribution:\n";
    echo "----------------------------\n";
    echo "Confirmed Deposits: {$depositStats['confirmed']['count']} users (".Number::currency($depositStats['confirmed']['amount']).")\n";
    echo "Pending Deposits: {$depositStats['pending']['count']} users (".Number::currency($depositStats['pending']['amount']).")\n";
    echo "Rejected Deposits: {$depositStats['rejected']['count']} users (".Number::currency($depositStats['rejected']['amount']).")\n\n";

    echo "Verification Package Distribution:\n";
    echo "--------------------------------\n";
    echo "With Product (1000 BDT): {$verificationStats['with_product']} users\n";
    echo "Without Product (500 BDT): {$verificationStats['without_product']} users\n";
    echo "--------------------------------\n";
    echo 'Total Amount: '.Number::currency(
        $depositStats['confirmed']['amount'] +
        $depositStats['pending']['amount'] +
        $depositStats['rejected']['amount']
    )."\n\n";

    // Reset time travel
    Carbon::setTestNow();
});
