<?php

declare(strict_types=1);

namespace Tancky\UpscaleCalculator;

use InvalidArgumentException;

/**
 * Immutable pixel dimensions of an image.
 */
final class Dimensions
{
    public function __construct(
        public readonly int $width,
        public readonly int $height,
    ) {
        if ($width < 1 || $height < 1) {
            throw new InvalidArgumentException('Width and height must be at least 1 pixel.');
        }
    }

    public function pixels(): int
    {
        return $this->width * $this->height;
    }

    public function megapixels(): float
    {
        return round($this->pixels() / 1_000_000, 2);
    }

    /**
     * Reduced aspect ratio, for example "16:9".
     */
    public function aspectRatio(): string
    {
        $divisor = self::gcd($this->width, $this->height);

        return intdiv($this->width, $divisor) . ':' . intdiv($this->height, $divisor);
    }

    /**
     * @return 'landscape'|'portrait'|'square'
     */
    public function orientation(): string
    {
        return match ($this->width <=> $this->height) {
            1 => 'landscape',
            -1 => 'portrait',
            default => 'square',
        };
    }

    /**
     * Multiply both sides by a factor, rounding to whole pixels.
     */
    public function scale(float $factor): self
    {
        if ($factor <= 0) {
            throw new InvalidArgumentException('Scale factor must be greater than 0.');
        }

        return new self(
            max(1, (int) round($this->width * $factor)),
            max(1, (int) round($this->height * $factor)),
        );
    }

    /**
     * Shrink to fit inside a bounding box while keeping the aspect ratio.
     * Images that already fit are returned unchanged.
     */
    public function fitWithin(int $maxWidth, int $maxHeight): self
    {
        if ($maxWidth < 1 || $maxHeight < 1) {
            throw new InvalidArgumentException('Bounding box must be at least 1x1 pixel.');
        }

        $ratio = min($maxWidth / $this->width, $maxHeight / $this->height);

        return $ratio >= 1 ? $this : $this->scale($ratio);
    }

    /**
     * Whether this image is at least as large as another on both sides.
     */
    public function covers(self $other): bool
    {
        return $this->width >= $other->width && $this->height >= $other->height;
    }

    public function __toString(): string
    {
        return $this->width . 'x' . $this->height;
    }

    private static function gcd(int $a, int $b): int
    {
        while ($b !== 0) {
            [$a, $b] = [$b, $a % $b];
        }

        return $a;
    }
}
