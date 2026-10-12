<?php

declare(strict_types=1);

namespace Tancky\UpscaleCalculator\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Tancky\UpscaleCalculator\Dimensions;

final class DimensionsTest extends TestCase
{
    public function testDescribesAnImage(): void
    {
        $image = new Dimensions(1920, 1080);

        self::assertSame(2_073_600, $image->pixels());
        self::assertSame(2.07, $image->megapixels());
        self::assertSame('16:9', $image->aspectRatio());
        self::assertSame('landscape', $image->orientation());
        self::assertSame('1920x1080', (string) $image);
    }

    public function testOrientation(): void
    {
        self::assertSame('portrait', (new Dimensions(1080, 1920))->orientation());
        self::assertSame('square', (new Dimensions(512, 512))->orientation());
    }

    public function testScale(): void
    {
        $scaled = (new Dimensions(1920, 1080))->scale(2);

        self::assertSame(3840, $scaled->width);
        self::assertSame(2160, $scaled->height);
    }

    public function testFitWithinShrinksAndKeepsRatio(): void
    {
        $fitted = (new Dimensions(1920, 1080))->fitWithin(960, 960);

        self::assertSame('960x540', (string) $fitted);
    }

    public function testFitWithinNeverEnlarges(): void
    {
        $image = new Dimensions(1920, 1080);

        self::assertSame($image, $image->fitWithin(4000, 4000));
    }

    public function testCovers(): void
    {
        $image = new Dimensions(1920, 1080);

        self::assertTrue($image->covers(new Dimensions(1920, 1080)));
        self::assertFalse($image->covers(new Dimensions(1921, 1080)));
    }

    public function testRejectsEmptyImage(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Dimensions(0, 10);
    }

    public function testRejectsNonPositiveScale(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new Dimensions(10, 10))->scale(0);
    }
}
