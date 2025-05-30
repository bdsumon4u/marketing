<?php

namespace App\Models;

use App\Enums\CompanyWalletType;
use Bavix\Wallet\Interfaces\Wallet as WalletInterface;
use Bavix\Wallet\Models\Wallet as BavixWallet;
use Bavix\Wallet\Traits\HasWalletFloat;
use Bavix\Wallet\Traits\HasWallets;
use Illuminate\Support\Str;

class Wallet extends BavixWallet implements WalletInterface
{
    use HasWalletFloat, HasWallets;

    private static $company = null;

    public static function company(): self
    {
        return self::$company ??= static::firstOrCreate(
            ['slug' => 'company'],
            [
                'name' => 'Company',
                'holder_type' => static::class,
                'uuid' => Str::uuid(),
                'holder_id' => 0,
            ]
        );
    }

    public static function createDefaultWallets(): void
    {
        foreach (CompanyWalletType::all() as $wallet) {
            if (self::company()->hasWallet($wallet['slug'])) {
                continue;
            }

            self::company()->createWallet([
                'name' => $wallet['name'],
                'slug' => $wallet['slug'],
                'meta' => ['percentage_share' => $wallet['percentage_share']],
            ]);
        }
    }
}
