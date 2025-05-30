<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;

enum CompanyWalletType: string implements HasColor
{
    case AM_FUND = 'am-fund';
    case BM_FUND = 'bm-fund';
    case CM_FUND = 'cm-fund';
    case DM_FUND = 'dm-fund';
    case EM_FUND = 'em-fund';
    case FM_FUND = 'fm-fund';
    case COMPANY = 'company';

    public function name(): string
    {
        return match ($this) {
            self::AM_FUND => 'AM Fund',
            self::BM_FUND => 'BM Fund',
            self::CM_FUND => 'CM Fund',
            self::DM_FUND => 'DM Fund',
            self::EM_FUND => 'EM Fund',
            self::FM_FUND => 'FM Fund',
            self::COMPANY => 'Company',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::AM_FUND => 'primary',
            self::BM_FUND => 'success',
            self::CM_FUND => 'info',
            self::DM_FUND => 'warning',
            self::EM_FUND => 'danger',
            self::FM_FUND => 'primary',
            self::COMPANY => 'primary',
        };
    }

    public function getIncentive(): float
    {
        return match ($this) {
            self::AM_FUND => 2.00,
            self::BM_FUND => 3.00,
            self::CM_FUND => 4.00,
            self::DM_FUND => 5.00,
            self::EM_FUND => 6.00,
            self::FM_FUND => 10.00,
            self::COMPANY => 20.00,
        } * config('mlm.registration_fee.without_product') / 100;
    }

    public static function all(): array
    {
        return array_map(fn ($case) => [
            'name' => $case->name(),
            'slug' => $case->value,
        ], self::cases());
    }
}
