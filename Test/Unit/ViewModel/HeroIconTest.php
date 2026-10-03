<?php
declare(strict_types=1);

namespace Panth\Core\Test\Unit\ViewModel;

use Magento\Framework\Escaper;
use Panth\Core\ViewModel\HeroIcon;
use PHPUnit\Framework\TestCase;

class HeroIconTest extends TestCase
{
    private HeroIcon $icons;

    protected function setUp(): void
    {
        $escaper = $this->createStub(Escaper::class);
        $escaper->method('escapeHtmlAttr')->willReturnCallback(
            fn ($value) => htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
        );
        $this->icons = new HeroIcon($escaper);
    }

    public function testOutlineIconUsesStrokeAndEscapedClass(): void
    {
        $svg = $this->icons->getIcon('menu', 'w-5 "h-5"');

        $this->assertStringStartsWith('<svg class="w-5 &quot;h-5&quot;" fill="none" stroke="currentColor"', $svg);
        $this->assertStringContainsString('viewBox="0 0 24 24"', $svg);
        $this->assertStringContainsString('aria-hidden="true"', $svg);
        $this->assertStringContainsString('focusable="false"', $svg);
        $this->assertStringEndsWith('</svg>', $svg);
    }

    public function testSolidIconHasNoStroke(): void
    {
        $svg = $this->icons->facebook();

        $this->assertStringContainsString('fill="currentColor"', $svg);
        $this->assertStringNotContainsString('stroke="currentColor"', $svg);
        $this->assertStringContainsString('class="w-6 h-6"', $svg);
    }

    public function testSpinnerGetsAnimationAndUnknownFallsBackToExclamation(): void
    {
        $this->assertStringContainsString('class="w-4 h-4 animate-spin"', $this->icons->spinner('w-4 h-4'));
        $this->assertSame($this->icons->getIcon('exclamation'), $this->icons->getIcon('does-not-exist'));
        $this->assertFalse($this->icons->hasIcon('does-not-exist'));
        $this->assertTrue($this->icons->hasIcon('shopping-cart'));
    }

    public function testEveryNamedHelperMatchesItsIconKey(): void
    {
        $map = [
            'menu' => 'menu', 'close' => 'close', 'search' => 'search', 'user' => 'user',
            'shoppingCart' => 'shopping-cart', 'heart' => 'heart', 'mail' => 'mail', 'phone' => 'phone',
            'chat' => 'chat', 'check' => 'check', 'checkCircle' => 'check-circle', 'xCircle' => 'x-circle',
            'exclamation' => 'exclamation', 'location' => 'location', 'clock' => 'clock',
            'arrowUp' => 'arrow-up', 'chevronDown' => 'chevron-down', 'chevronUp' => 'chevron-up',
            'whatsapp' => 'whatsapp', 'spinner' => 'spinner', 'facebook' => 'facebook', 'twitter' => 'twitter',
            'instagram' => 'instagram', 'linkedin' => 'linkedin', 'youtube' => 'youtube', 'pinterest' => 'pinterest',
        ];
        foreach ($map as $method => $key) {
            if (!method_exists($this->icons, $method)) {
                continue;
            }
            $this->assertSame($this->icons->getIcon($key, 'c'), $this->icons->$method('c'), $method);
        }
        $available = $this->icons->getAvailableIcons();
        $this->assertContains('chevron-right', $available);
        $this->assertCount(count(array_unique($available)), $available);
        foreach ($available as $name) {
            $svg = $this->icons->getIcon($name);
            $this->assertMatchesRegularExpression('/^<svg [^>]+>.+<\/svg>$/s', $svg, $name);
            $previous = libxml_use_internal_errors(true);
            $xml = simplexml_load_string($svg);
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
            $this->assertNotFalse($xml, $name);
        }
    }
}
