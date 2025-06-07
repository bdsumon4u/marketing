<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum UserRank: int implements HasLabel
{
    case _M = 0;
    case AM = 1;
    case BM = 2;
    case CM = 3;
    case DM = 4;
    case EM = 5;
    case FM = 6;

    public static function values(): array
    {
        return array_map(fn (UserRank $rank) => $rank->value, self::cases());
    }

    public static function getMaximumRank(): int
    {
        return self::FM->value;
    }

    public function getLabel(): string
    {
        return $this->name;
    }
}
