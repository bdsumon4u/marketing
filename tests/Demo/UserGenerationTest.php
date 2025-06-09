<?php

namespace Tests\Demo;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Carbon;

class UserGenerationTest extends TestCase
{
    protected int $minutesBetweenUsers;

    protected function setUp(): void
    {
        parent::setUp();

        // Calculate minutes between users
        $totalMinutes = $this->days * 24 * 60;
        $this->minutesBetweenUsers = ceil($totalMinutes / $this->totalUsers);
    }

    public function test_can_generate_users_with_proper_time_distribution()
    {
        for ($i = 0; $i < $this->totalUsers; $i++) {
            $currentDate = $this->startDate->copy()->addMinutes($i * $this->minutesBetweenUsers);
            Carbon::setTestNow($currentDate);

            $user = User::factory()->create([
                'email' => "user{$i}@example.com",
                'created_at' => $currentDate,
            ])->fresh();

            // Assert user was created with correct data
            expect($user)
                ->toBeInstanceOf(User::class)
                ->email->toBe("user{$i}@example.com")
                ->created_at->toBeInstanceOf(CarbonImmutable::class);

            // Assert time distribution
            $expectedTime = $this->startDate->copy()->addMinutes($i * $this->minutesBetweenUsers);
            expect($user->created_at->timestamp)->toBe($expectedTime->timestamp);
        }

        echo "\nGenerated {$this->totalUsers} users with proper time distribution\n";
    }
}
