<?php
/**
 * Panth Core - Color Helper
 *
 * Provides color manipulation utilities for OKLCH, HEX, RGB color formats.
 * Used across all Panth modules for consistent color handling.
 *
 * @category  Panth
 * @package   Panth_Core
 * @author    Panth Infotech
 */
declare(strict_types=1);

namespace Panth\Core\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Color extends AbstractHelper
{
    /**
     * Convert hex color to OKLCH format
     *
     * @param string $hex Hex color code (with or without #)
     * @return string OKLCH color string
     */
    public function hexToOklch(string $hex): string
    {
        // Remove # if present
        $hex = ltrim($hex, '#');

        // Convert hex to RGB
        $r = hexdec(substr($hex, 0, 2)) / 255;
        $g = hexdec(substr($hex, 2, 2)) / 255;
        $b = hexdec(substr($hex, 4, 2)) / 255;

        // Simple approximation - for production, use proper color space conversion
        $lightness = ($r + $g + $b) / 3 * 100;
        $chroma = max($r, $g, $b) - min($r, $g, $b);

        // Calculate hue
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

    /**
     * Validate if string is valid hex color
     *
     * @param string $hex Color string to validate
     * @return bool
     */
    public function isValidHex(string $hex): bool
    {
        return (bool)preg_match('/^#?[0-9A-Fa-f]{6}$/', $hex);
    }

    /**
     * Validate if string is valid OKLCH color
     *
     * @param string $oklch Color string to validate
     * @return bool
     */
    public function isValidOklch(string $oklch): bool
    {
        return (bool)preg_match('/^oklch\([0-9.]+%?\s+[0-9.]+\s+[0-9.]+\)$/i', $oklch);
    }

    /**
     * Get CSS gradient style from two colors
     *
     * @param string $fromColor Start color
     * @param string $toColor End color
     * @param string $direction Gradient direction (default: 'to right')
     * @return string CSS gradient style
     */
    public function getGradientStyle(string $fromColor, string $toColor, string $direction = 'to right'): string
    {
        return "background: linear-gradient({$direction}, {$fromColor}, {$toColor});";
    }

    /**
     * Lighten a color by percentage
     *
     * @param string $hex Hex color code
     * @param int $percent Percentage to lighten (0-100)
     * @return string Lightened hex color
     */
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

    /**
     * Darken a color by percentage
     *
     * @param string $hex Hex color code
     * @param int $percent Percentage to darken (0-100)
     * @return string Darkened hex color
     */
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
