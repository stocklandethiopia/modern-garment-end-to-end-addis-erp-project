<?php

namespace App\Services\Support;

use InvalidArgumentException;

final class DecimalQuantity
{
    public static function units(string|int|float $value): int
    {
        $raw = trim((string) $value);
        if (! preg_match('/^(\d+)(?:\.(\d{1,6}))?$/', $raw, $matches)) {
            throw new InvalidArgumentException('Quantity must be a non-negative decimal with at most six fractional digits.');
        }

        return ((int) $matches[1] * 1_000_000)
            + (int) str_pad($matches[2] ?? '', 6, '0', STR_PAD_RIGHT);
    }

    public static function format(int $units): string
    {
        $negative = $units < 0;
        $absolute = abs($units);
        $formatted = intdiv($absolute, 1_000_000).'.'.str_pad((string) ($absolute % 1_000_000), 6, '0', STR_PAD_LEFT);

        return $negative ? '-'.$formatted : $formatted;
    }

    public static function add(string|int|float $left, string|int|float $right): string
    {
        return self::format(self::units($left) + self::units($right));
    }

    public static function subtract(string|int|float $left, string|int|float $right): string
    {
        return self::format(self::units($left) - self::units($right));
    }

    public static function exceeds(string|int|float $value, string|int|float $limit): bool
    {
        return self::units($value) > self::units($limit);
    }

    public static function equal(string|int|float $left, string|int|float $right): bool
    {
        return self::units($left) === self::units($right);
    }
}
