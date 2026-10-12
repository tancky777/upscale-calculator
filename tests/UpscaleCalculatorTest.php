<?php

declare(strict_types=1);

namespace Tancky\UpscaleCalculator\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Tancky\UpscaleCalculator\Dimensions;
use Tancky\UpscaleCalculator\UpscaleCalculator;

final class UpscaleCalculatorTest extends TestCase
{
    public function testFactorsAreSortedAndDeduplicated(): void
    {
        $calculator = new UpscaleCalculator([4, 2, 2.0, 8]);

        self::assertSame([2.0, 4.0, 8.0], $calculator->factors());
    }

    public function testExactFactor(): void
    {
        $calculator = new UpscaleCalculator();

        self::assertSame(3.0, $calculator->exactFactor(new Dimensions(1000, 800), new Dimensions(3000, 2000)));
    }

    public function testFactorForPicksSmallestSufficientFactor(): void
    {
        $calculator = new UpscaleCalculator();
        $source = new Dimensions(1000, 800);

        self::assertSame(4.0, $calculator->factorFor($source, new Dimensions(3000, 2000)));
        self::assertSame(2.0, $calculator->factorFor($source, new Dimensions(2000, 1600)));
    }

    public function testFactorForReturnsOneWhenSourceIsLargeEnough(): void
    {
        $calculator = new UpscaleCalculator();

        self::assertSame(1.0, $calculator->factorFor(new Dimensions(1000, 800), new Dimensions(500, 500)));
    }

    public function testFactorForReturnsNullWhenOutOfReach(): void
    {
        $calculator = new UpscaleCalculator();

        self::assertNull($calculator->factorFor(new Dimensions(1000, 800), new Dimensions(10000, 10000)));
    }

    public function testPixelsForPrint(): void
    {
        $calculator = new UpscaleCalculator();

        self::assertSame('2400x3000', (string) $calculator->pixelsForPrint(8, 10));
        self::assertSame('1200x1500', (string) $calculator->pixelsForPrint(8, 10, 150));
    }

    public function testPrintSize(): void
    {
        $calculator = new UpscaleCalculator();

        self::assertSame(
            ['width_in' => 10.0, 'height_in' => 8.0, 'width_cm' => 25.4, 'height_cm' => 20.32],
            $calculator->printSize(new Dimensions(3000, 2400)),
        );
    }

    public function testFactorForPrint(): void
    {
        $calculator = new UpscaleCalculator();

        self::assertSame(2.0, $calculator->factorForPrint(new Dimensions(1200, 1500), 8, 10));
    }

    public function testRejectsEmptyFactorList(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new UpscaleCalculator([]);
    }

    public function testRejectsFactorsThatDoNotEnlarge(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new UpscaleCalculator([1, 2]);
    }

    public function testRejectsInvalidDpi(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new UpscaleCalculator())->printSize(new Dimensions(100, 100), 0);
    }
}
