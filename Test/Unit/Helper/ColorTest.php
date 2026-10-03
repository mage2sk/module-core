<?php
declare(strict_types=1);

namespace Panth\Core\Test\Unit\Helper;

use Magento\Framework\App\Helper\Context;
use Panth\Core\Helper\Color;
use PHPUnit\Framework\TestCase;

class ColorTest extends TestCase
{
    private Color $color;

    protected function setUp(): void
    {
        $this->color = new Color($this->createStub(Context::class));
    }

    public function testValidatesHexAndOklchInputs(): void
    {
        $this->assertTrue($this->color->isValidHex('#1a2B3c'));
        $this->assertTrue($this->color->isValidHex('1a2b3c'));
        $this->assertFalse($this->color->isValidHex('#1a2b3'));
        $this->assertFalse($this->color->isValidHex('#gggggg'));
        $this->assertTrue($this->color->isValidOklch('oklch(62% 0.21 250)'));
        $this->assertTrue($this->color->isValidOklch('OKLCH(0.62 0.21 250)'));
        $this->assertFalse($this->color->isValidOklch('rgb(1 2 3)'));
    }

    public function testLightenAndDarkenMoveTowardsTheExtremes(): void
    {
        $this->assertSame('#ffffff', $this->color->lighten('#336699', 100));
        $this->assertSame('#336699', $this->color->lighten('#336699', 0));
        $this->assertSame('#000000', $this->color->darken('#336699', 100));
        $this->assertSame('#19334c', $this->color->darken('#336699', 50));
    }

    public function testHexToOklchProducesCssFunctionSyntax(): void
    {
        $this->assertSame('oklch(100% 0.00 0)', $this->color->hexToOklch('#ffffff'));
        $this->assertSame('oklch(33% 1.00 0)', $this->color->hexToOklch('#ff0000'));
        $this->assertMatchesRegularExpression('/^oklch\(\d+% \d\.\d\d \d+\)$/', $this->color->hexToOklch('#336699'));
    }

    public function testGradientStyleUsesGivenDirection(): void
    {
        $this->assertSame(
            'background: linear-gradient(to bottom, #000000, #ffffff);',
            $this->color->getGradientStyle('#000000', '#ffffff', 'to bottom')
        );
    }
}
