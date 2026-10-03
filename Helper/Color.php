<?php
declare(strict_types=1);

namespace Panth\Core\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Color extends AbstractHelper
{
    public function hexToOklch(string $hex): string
    {
        $hex = ltrim($hex, '#');

        $r = hexdec(substr($hex, 0, 2)) / 255;
        $g = hexdec(substr($hex, 2, 2)) / 255;
        $b = hexdec(substr($hex, 4, 2)) / 255;

        $lightness = ($r + $g + $b) / 3 * 100;
        $chroma = max($r, $g, $b) - min($r, $g, $b);

        $max = max($r, $g, $b);
        $hue = 0;
        if ($chroma > 0) {
            if ($max === $r) {
                $hue = fmod((($g - $b) / $chroma), 6) * 60;
            } elseif ($max === $g) {
                $hue = ((($b - $r) / $chroma) + 2) * 60;
            } else {
                $hue = ((($r - $g) / $chroma) + 4) * 60;
            }
        }

        if ($hue < 0) {
            $hue += 360;
        }

        return sprintf('oklch(%.0f%% %.2f %.0f)', $lightness, $chroma, $hue);
    }

    public function isValidHex(string $hex): bool
    {
        return (bool)preg_match('/^#?[0-9A-Fa-f]{6}$/', $hex);
    }

    public function isValidOklch(string $oklch): bool
    {
        return (bool)preg_match('/^oklch\([0-9.]+%?\s+[0-9.]+\s+[0-9.]+\)$/i', $oklch);
    }

    public function getGradientStyle(string $fromColor, string $toColor, string $direction = 'to right'): string
    {
        return "background: linear-gradient({$direction}, {$fromColor}, {$toColor});";
    }

    public function lighten(string $hex, int $percent): string
    {
        $hex = ltrim($hex, '#');

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        $r = min(255, $r + (255 - $r) * $percent / 100);
        $g = min(255, $g + (255 - $g) * $percent / 100);
        $b = min(255, $b + (255 - $b) * $percent / 100);

        return sprintf('#%02x%02x%02x', (int)$r, (int)$g, (int)$b);
    }

    public function darken(string $hex, int $percent): string
    {
        $hex = ltrim($hex, '#');

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        $r = max(0, $r - ($r * $percent / 100));
        $g = max(0, $g - ($g * $percent / 100));
        $b = max(0, $b - ($b * $percent / 100));

        return sprintf('#%02x%02x%02x', (int)$r, (int)$g, (int)$b);
    }
}
