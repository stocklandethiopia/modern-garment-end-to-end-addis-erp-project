<?php

namespace Tests\Unit;

use App\Services\Support\DecimalQuantity;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class DecimalQuantityTest extends TestCase
{
    public function test_add_and_subtract_preserve_six_digit_precision(): void
    {
        self::assertSame('3.750001', DecimalQuantity::add('1.250000', '2.500001'));
        self::assertSame('0.000001', DecimalQuantity::subtract('1.000000', '0.999999'));
    }

    public function test_equal_and_exceeds_use_integer_units_not_binary_float_comparison(): void
    {
        self::assertTrue(DecimalQuantity::equal('0.300000', '0.3'));
        self::assertFalse(DecimalQuantity::exceeds('0.300000', '0.3'));
        self::assertTrue(DecimalQuantity::exceeds('0.300001', '0.3'));
    }

    public function test_format_round_trips_fractional_units(): void
    {
        self::assertSame('12.000042', DecimalQuantity::format(12_000_042));
        self::assertSame('0.000001', DecimalQuantity::format(1));
        self::assertSame('0.000000', DecimalQuantity::format(0));
    }

    public function test_negative_or_over_precision_quantities_are_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        DecimalQuantity::units('-0.01');
    }

    public function test_more_than_six_fractional_digits_are_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        DecimalQuantity::units('0.1234567');
    }
}
