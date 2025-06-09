<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\User;
use App\Models\Wallet;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Admin::create([
            'name' => 'Mr. Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);

        Wallet::createDefaultWallets();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // User::factory(100)->create();
    }
}
