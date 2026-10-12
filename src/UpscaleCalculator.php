<?php

declare(strict_types=1);

namespace Tancky\UpscaleCalculator;

use InvalidArgumentException;

/**
 * Works out which upscale factor an image needs for a target size or print.
 */
final class UpscaleCalculator
{
    public const DEFAULT_FACTORS = [2.0, 4.0, 6.0];

    private const CM_PER_INCH = 2.54;

    /** @var list<float> */
    private array $factors;

    /**
     * @param list<int|float> $factors Upscale factors your upscaler supports, each greater than 1.
     */
    public function __construct(array $factors = self::DEFAULT_FACTORS)
    {
        if ($factors === []) {
            throw new InvalidArgumentException('At least one upscale factor is required.');
        }

        $normalized = [];
        foreach ($factors as $factor) {
            if ((!is_int($factor) && !is_float($factor)) || $factor <= 1) {
                throw new InvalidArgumentException('Upscale factors must be numbers greater than 1.');
            }
            $normalized[] = (float) $factor;
        }

        sort($normalized);
        $this->factors = array_values(array_unique($normalized));
    }

    /**
     * @return list<float>
     */
    public function factors(): array
    {
        return $this->factors;
    }

    /**
     * Exact multiplier needed for the source to cover the target on both sides.
     */
    public function exactFactor(Dimensions $source, Dimensions $target): float
    {
        return max($target->width / $source->width, $target->height / $source->height);
    }

    /**
     * Smallest supported factor that makes the source cover the target.
     * Returns 1.0 when no upscaling is needed and null when no supported factor is enough.
     */
    public function factorFor(Dimensions $source, Dimensions $target): ?float
    {
        if ($source->covers($target)) {
            return 1.0;
        }

        foreach ($this->factors as $factor) {
            if ($source->scale($factor)->covers($target)) {
                return $factor;
            }
        }

        return null;
    }

    /**
     * Pixel dimensions required to print at a physical size.
     */
    public function pixelsForPrint(float $widthInches, float $heightInches, int $dpi = 300): Dimensions
    {
        if ($widthInches <= 0 || $heightInches <= 0) {
            throw new InvalidArgumentException('Print size must be greater than 0.');
        }
        self::assertDpi($dpi);

        return new Dimensions(
            (int) ceil($widthInches * $dpi),
            (int) ceil($heightInches * $dpi),
        );
    }

    /**
     * Largest print an image supports at a given DPI.
     *
     * @return array{width_in: float, height_in: float, width_cm: float, height_cm: float}
     */
    public function printSize(Dimensions $image, int $dpi = 300): array
    {
        self::assertDpi($dpi);

        $widthInches = $image->width / $dpi;
        $heightInches = $image->height / $dpi;

        return [
            'width_in' => round($widthInches, 2),
            'height_in' => round($heightInches, 2),
            'width_cm' => round($widthInches * self::CM_PER_INCH, 2),
            'height_cm' => round($heightInches * self::CM_PER_INCH, 2),
        ];
    }

    /**
     * Smallest supported factor that makes the source printable at a physical size.
     * Returns 1.0 when no upscaling is needed and null when no supported factor is enough.
     */
    public function factorForPrint(
        Dimensions $source,
        float $widthInches,
        float $heightInches,
        int $dpi = 300,
    ): ?float {
        return $this->factorFor($source, $this->pixelsForPrint($widthInches, $heightInches, $dpi));
    }

    private static function assertDpi(int $dpi): void
    {
        if ($dpi < 1) {
            throw new InvalidArgumentException('DPI must be at least 1.');
        }
    }
}
