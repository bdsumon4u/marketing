<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Traits\HasWallet;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser, MustVerifyEmail, Wallet
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasWallet, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'referral_code',
        'referred_by',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function (User $user) {
            if (! $user->referral_code) {
                $user->referral_code = $user->generateReferralCode();
            }
        });
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(User::class, 'referred_by');
    }

    public function generateReferralCode(): string
    {
        // Characters that are easy to read and distinguish
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // Removed I, O, 0, 1
        $code = '';

        do {
            $code = '';
            for ($i = 0; $i < 8; $i++) {
                $code .= $chars[random_int(0, strlen($chars) - 1)];
            }
        } while (static::where('referral_code', $code)->exists());

        return $code;
    }

    public function getReferralIncentive(int $level): float
    {
        return match ($level) {
            1 => 25.0, // 25%
            2 => 5.0,  // 5%
            3 => 4.0,  // 4%
            4, 5 => 3.0, // 3%
            default => 2.0, // 2%
        };
    }

    public function hasReachedReferralLimit(): bool
    {
        $maxReferrals = config('mlm.max_referrals_per_user', 4);

        return $this->referrals()->count() >= $maxReferrals;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }
}
