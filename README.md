# Upscale Calculator

[![tests](https://github.com/tancky777/upscale-calculator/actions/workflows/tests.yml/badge.svg)](https://github.com/tancky777/upscale-calculator/actions/workflows/tests.yml)

Small, dependency-free PHP helpers for the math around image upscaling:

- scaled pixel dimensions, megapixels and aspect ratio
- the smallest upscale factor (2x, 4x, 6x, or your own list) that reaches a target size
- pixel requirements and maximum print size at a given DPI

The library only does arithmetic. It makes no network requests and does not read or write image files.

## Install

```bash
composer require tancky/upscale-calculator
```

Requires PHP 8.1 or newer.

## Usage

```php
use Tancky\UpscaleCalculator\Dimensions;
use Tancky\UpscaleCalculator\UpscaleCalculator;

$photo = new Dimensions(1200, 1500);

$photo->megapixels();   // 1.8
$photo->aspectRatio();  // "4:5"
$photo->orientation();  // "portrait"
(string) $photo->scale(4);            // "4800x6000"
(string) $photo->fitWithin(600, 600); // "480x600"

$calculator = new UpscaleCalculator(); // supports 2x, 4x and 6x by default

// Which factor do I need for a 4K wallpaper?
$calculator->factorFor($photo, new Dimensions(3840, 2160)); // 4.0

// Which factor do I need for an 8x10 inch print at 300 DPI?
$calculator->pixelsForPrint(8, 10);          // 2400x3000
$calculator->factorForPrint($photo, 8, 10);  // 2.0

// How large can I print the original?
$calculator->printSize($photo);
// ['width_in' => 4.0, 'height_in' => 5.0, 'width_cm' => 10.16, 'height_cm' => 12.7]
```

`factorFor()` and `factorForPrint()` return `1.0` when the image is already large enough and `null` when none of the supported factors is enough.

Pass your own factors if your upscaler supports a different set:

```php
$calculator = new UpscaleCalculator([2, 3, 8]);
```

## Testing

```bash
composer install
composer test
```

## About

Maintained by [Tancky AI](https://tancky.io). The default factors match the 2x, 4x and 6x options of the [Tancky AI Image Upscaler](https://tancky.io/ai-image-upscaler), where the same calculations decide which option an image needs.

## License

MIT
