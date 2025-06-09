<?php

namespace Tests\Demo;

use App\Models\Admin;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification as NotificationFacade;

abstract class TestCase extends \Tests\TestCase
{
    protected Admin $admin;

    protected Carbon $startDate;

    protected int $totalUsers = 1000;

    protected int $days = 60;

    protected function setUp(): void
    {
        parent::setUp();

        // Fake notifications
        NotificationFacade::fake();

        // Create an admin user for deposit approval
        $this->admin = Admin::query()->firstOrCreate([
            'email' => 'admin@example.com',
        ], [
            'name' => 'Mr. Admin',
            'password' => bcrypt('password'),
        ]);

        // Set start date
        $this->startDate = Carbon::now()->subDays($this->days);
    }

    protected function tearDown(): void
    {
        // Reset time travel
        Carbon::setTestNow();

        parent::tearDown();
    }
}
