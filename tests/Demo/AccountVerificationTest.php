<?php

namespace Tests\Demo;

use App\Models\User;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

class AccountVerificationTest extends TestCase
{
    protected array $stats = [
        'with_product' => 0,
        'without_product' => 0,
        'unverified' => 0,
    ];

    public function test_can_verify_accounts_with_correct_distribution()
    {
        $users = User::with('wallet')->get();

        foreach ($users as $user) {
            Carbon::setTestNow($user->created_at); // Time-travel to user creation time
            $this->verifyUser($user);
        }

        // Print statistics
        echo "\nVerification Package Distribution:\n";
        echo "--------------------------------\n";
        echo "With Product (1000 BDT): {$this->stats['with_product']} users\n";
        echo "Without Product (500 BDT): {$this->stats['without_product']} users\n";
        echo "Unverified: {$this->stats['unverified']} users\n";
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

        Livewire::actingAs($user)
            ->test(\App\Livewire\VerifyNowModal::class)
            ->set('data.package', $package)
            ->call('submit');

        if ($withProduct) {
            $this->stats['with_product']++;
        } else {
            $this->stats['without_product']++;
        }
    }
}
