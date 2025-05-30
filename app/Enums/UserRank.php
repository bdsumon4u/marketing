<?php

namespace App\Enums;

enum UserRank: int
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
}
