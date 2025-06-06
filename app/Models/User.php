<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Casts\Money;
use App\Enums\UserRank;
use Bavix\Wallet\Interfaces\Confirmable;
use Bavix\Wallet\Interfaces\Wallet as WalletInterface;
use Bavix\Wallet\Interfaces\WalletFloat as WalletFloatInterface;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Wallet as WalletModel;
use Bavix\Wallet\Traits\CanConfirm;
use Bavix\Wallet\Traits\HasWalletFloat;
use Bavix\Wallet\Traits\HasWallets;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable implements Confirmable, FilamentUser, MustVerifyEmail, WalletFloatInterface, WalletInterface
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use CanConfirm, HasFactory, HasWalletFloat, HasWallets, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'referrer_id',
        'name',
        'username',
        'email',
        'phone',
        'password',
        'rank',
        'is_active',
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
            'is_active' => 'boolean',
            'rank' => UserRank::class,
            'total_deposit' => Money::class,
            'total_income' => Money::class,
            'total_withdraw' => Money::class,
            'referral_income' => Money::class,
            'generation_income' => Money::class,
            'rank_income' => Money::class,
            'magic_income' => Money::class,
            'pending_deposit' => Money::class,
            'rejected_deposit' => Money::class,
            'pending_withdraw' => Money::class,
            'rejected_withdraw' => Money::class,
            'total_send' => Money::class,
            'total_receive' => Money::class,
        ];
    }

    public static function baseId(): int
    {
        return 1001;
    }

    public function parentId(): ?int
    {
        if ($this->id === static::baseId()) {
            return null;
        }

        return ($this->id + static::baseId() - 1) >> 1;
    }

    public function leftId(): int
    {
        return 2 * $this->id - static::baseId() + 1;
    }

    public function rightId(): int
    {
        return 2 * $this->id - static::baseId() + 2;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function (User $user) {
            if (! $user->username) {
                $user->username = $user->generateUsername();
            }
        });
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(User::class, 'referrer_id');
    }

    public function generateUsername(): string
    {
        // Characters that are easy to read and distinguish
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // Removed I, O, 0, 1
        $code = '';

        do {
            $code = '';
            for ($i = 0; $i < 8; $i++) {
                $code .= $chars[random_int(0, strlen($chars) - 1)];
            }
        } while (static::where('username', $code)->exists());

        return $code;
    }

    public function getOrCreateWallet(string $slug = 'default'): WalletModel
    {
        return $this->wallets()->firstOrCreate(
            ['slug' => $slug],
            [
                'name' => Str::title(Str::replace('-', ' ', $slug)).' Wallet',
            ]
        );
    }

    public function hasPendingDeposit(int $amount, ?int $minutes = null): bool
    {
        if ($this->pending_deposit < $amount) {
            return false;
        }

        $query = $this->transactions()
            ->where('type', Transaction::TYPE_DEPOSIT)
            ->where('confirmed', false)
            ->where('amount', $amount * 100);

        if ($minutes) {
            $query->where('created_at', '>', now()->subMinutes($minutes));
        }

        return $query->exists();
    }

    public function getReferralIncentive(int $level): float
    {
        if (! $this->is_active || ($level > 1 && $this->referrals_count < 3)) {
            return 0;
        }

        return match ($level) {
            1 => 20.0, // 20%
            2 => 5.0,  // 5%
            3 => 3.0,  // 3%
            4, 5, 6, 7 => 2.0, // 2%
            8 => 1.6, // 1.6%
            9 => 1.4, // 1.4%
            10 => 1.0, // 1%
            default => 0, // 0%
        } * config('mlm.registration_fee.without_product') / 100;
    }

    public function getMagicIncome(): float
    {
        return 1.0 * config('mlm.registration_fee.without_product') / 100;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }
}
