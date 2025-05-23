<?php

namespace App\Enums;

enum CompanyWalletType: string
{
    case COMPANY = 'company';
    case AM_FUND = 'am-fund';
    case BM_FUND = 'bm-fund';
    case CM_FUND = 'cm-fund';
    case DM_FUND = 'dm-fund';
    case EM_FUND = 'em-fund';

    public function name(): string
    {
        return match ($this) {
            self::COMPANY => 'Company',
            self::AM_FUND => 'AM Fund',
            self::BM_FUND => 'BM Fund',
            self::CM_FUND => 'CM Fund',
            self::DM_FUND => 'DM Fund',
            self::EM_FUND => 'EM Fund',
        };
    }

    public function percentageShare(): float
    {
        return match ($this) {
            self::COMPANY => 25.00,
            self::AM_FUND => 2.50,
            self::BM_FUND => 3.50,
            self::CM_FUND => 4.00,
            self::DM_FUND => 5.00,
            self::EM_FUND => 10.00,
        };
    }

    public static function all(): array
    {
        return array_map(fn ($case) => [
            'name' => $case->name(),
            'slug' => $case->value,
            'percentage_share' => $case->percentageShare(),
        ], self::cases());
    }
}
