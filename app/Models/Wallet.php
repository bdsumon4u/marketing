<?php

namespace App\Models;

use App\Enums\CompanyWalletType;
use Bavix\Wallet\Models\Wallet as BavixWallet;
use Bavix\Wallet\Traits\HasWallets;
use Illuminate\Support\Str;

class Wallet extends BavixWallet
{
    use HasWallets;

    private static $company = null;

    public static function company(): self
    {
        return self::$company ??= static::firstOrCreate(
            ['slug' => CompanyWalletType::COMPANY->value],
            [
                'name' => CompanyWalletType::COMPANY->name(),
                'holder_type' => static::class,
                'uuid' => Str::uuid(),
                'holder_id' => 0,
            ]
        );
    }

    public static function createDefaultWallets(): void
    {
        foreach (CompanyWalletType::all() as $wallet) {
            self::company()->createWallet([
                'name' => $wallet['name'],
                'slug' => $wallet['slug'],
                'meta' => ['percentage_share' => $wallet['percentage_share']],
            ]);
        }
    }
}
