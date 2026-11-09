<?php

namespace App\Support;

use InvalidArgumentException;

final class TicketMoney
{
    public static function minor(mixed $amount): int
    {
        if ((! is_string($amount) && ! is_int($amount))
            || ! preg_match('/^(\d{1,10})(?:\.(\d{1,2}))?$/D', (string) $amount, $matches)) {
            throw new InvalidArgumentException('Tarif harus berupa nominal desimal nonnegatif yang valid.');
        }

        return ((int) $matches[1] * 100) + (int) str_pad($matches[2] ?? '', 2, '0');
    }

    public static function decimal(int $minor): string
    {
        return intdiv($minor, 100).'.'.str_pad((string) ($minor % 100), 2, '0', STR_PAD_LEFT);
    }
}
