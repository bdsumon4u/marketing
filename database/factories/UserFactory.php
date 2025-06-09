<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (User $user) {
            $rankThreshold = config('mlm.rank_threshold');
            $baseId = User::baseId();
            $maxReferrals = $rankThreshold + 2;

            // Get the n most recent users who have reached the rank threshold
            $qualifiedReferrers = User::whereHas('referrals', function ($query) {}, '>=', $rankThreshold)
                ->latest()
                ->take($rankThreshold)
                ->get();

            if ($qualifiedReferrers->count() === $rankThreshold) {
                // Get all referrals of these n most recent qualified users
                // but only include users who haven't reached max referrals
                $referralIds = $qualifiedReferrers->flatMap(function ($user) use ($maxReferrals) {
                    return $user->referrals()
                        ->whereDoesntHave('referrals', function ($query) use ($maxReferrals) {
                            $query->select('referrer_id')
                                ->groupBy('referrer_id')
                                ->havingRaw('COUNT(*) >= ?', [$maxReferrals]);
                        })
                        ->pluck('id');
                })->toArray();

                if (! empty($referralIds)) {
                    $user->update([
                        'referrer_id' => fake()->randomElement($referralIds),
                    ]);

                    return;
                }
            }

            // If no qualified referrers or their referrals, assign from first n users
            // but only include users who haven't reached max referrals
            $availableReferrers = User::whereBetween('id', [$baseId, min($baseId + $rankThreshold - 1, User::max('id') - 1)])
                ->whereDoesntHave('referrals', function ($query) use ($maxReferrals) {
                    $query->select('referrer_id')
                        ->groupBy('referrer_id')
                        ->havingRaw('COUNT(*) >= ?', [$maxReferrals]);
                })
                ->pluck('id')
                ->toArray();

            if (! empty($availableReferrers)) {
                $user->update([
                    'referrer_id' => fake()->optional()->randomElement($availableReferrers),
                ]);

                return;
            }

            // If no available referrers, set to null
            $user->update(['referrer_id' => null]);
        });
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'username' => fake()->unique()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->unique()->e164PhoneNumber(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
