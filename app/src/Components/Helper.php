<?php

declare(strict_types = 1);

namespace Teftely\Components;

class Helper
{
    public static function plural($number, $after): string
    {
        $cases = [2, 0, 1, 1, 1, 2];

        return $after[($number % 100 > 4 && $number % 100 < 20) ? 2 : $cases[min($number % 10, 5)]];
    }
}
