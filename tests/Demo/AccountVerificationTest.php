<?php

namespace Tests\Demo;

use App\Models\User;
use Livewire\Livewire;

class AccountVerificationTest extends TestCase
{
    protected array $stats = [
        'with_product' => 0,
        'without_product' => 0,
    ];

    public function test_can_verify_accounts_with_correct_distribution()
    {
        $users = User::orderBy('id')->get();
        expect($users)->toHaveCount($this->totalUsers);

        foreach ($users as $user) {
            // Only verify if deposit was confirmed
            if ($user->fresh()->is_active) {
                $this->verifyUser($user);

                // Assert verification was successful
                expect($user->fresh())
                    ->is_active->toBeTrue()
                    ->with_product->toBeBool();
            }
        }

        // Assert verification distribution
        $totalVerified = $this->stats['with_product'] + $this->stats['without_product'];
        $withProductPercentage = ($this->stats['with_product'] / $totalVerified) * 100;
        $withoutProductPercentage = ($this->stats['without_product'] / $totalVerified) * 100;

        // Should be roughly 50-50 split
        expect($withProductPercentage)->toBeGreaterThanOrEqual(40)->toBeLessThanOrEqual(60);
        expect($withoutProductPercentage)->toBeGreaterThanOrEqual(40)->toBeLessThanOrEqual(60);

        // Print statistics
        echo "\nVerification Package Distribution:\n";
        echo "--------------------------------\n";
        echo "With Product (1000 BDT): {$this->stats['with_product']} users\n";
        echo "Without Product (500 BDT): {$this->stats['without_product']} users\n";
    }

    protected function verifyUser(User $user): void
    {
        if (! $user->is_active) {
            $withProduct = rand(0, 1) === 1;
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
}
