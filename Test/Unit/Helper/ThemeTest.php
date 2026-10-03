<?php
declare(strict_types=1);

namespace Panth\Core\Test\Unit\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\DesignInterface;
use Panth\Core\Helper\Theme;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ThemeTest extends TestCase
{
    private function helper(bool $hyvaModule, ?string $themePath, ?\Exception $designError = null): Theme
    {
        $design = $this->createStub(DesignInterface::class);
        if ($designError) {
            $design->method('getDesignTheme')->willThrowException($designError);
        } elseif ($themePath === null) {
            $design->method('getDesignTheme')->willReturn(null);
        } else {
            $theme = $this->createStub(ThemeInterface::class);
            $theme->method('getThemePath')->willReturn($themePath);
            $design->method('getDesignTheme')->willReturn($theme);
        }
        $moduleManager = $this->createStub(ModuleManager::class);
        $moduleManager->method('isEnabled')->willReturnCallback(fn ($name) => $name === 'Hyva_Theme' && $hyvaModule);

        return new Theme($this->createStub(Context::class), $design, $moduleManager);
    }

    public static function themeProvider(): array
    {
        return [
            'hyva child theme' => [true, 'Panth/Infotech', Theme::THEME_HYVA],
            'hyva default' => [true, 'Hyva/default', Theme::THEME_HYVA],
            'luma store view on hyva install' => [true, 'Magento/luma', Theme::THEME_LUMA],
            'blank store view on hyva install' => [true, 'Magento/blank', Theme::THEME_LUMA],
            'no hyva module, hyva named path' => [false, 'Vendor/hyva-child', Theme::THEME_HYVA],
            'no hyva module, luma' => [false, 'Magento/luma', Theme::THEME_LUMA],
            'no theme resolved, hyva module' => [true, null, Theme::THEME_HYVA],
            'no theme resolved, no hyva' => [false, null, Theme::THEME_LUMA],
        ];
    }

    #[DataProvider('themeProvider')]
    public function testDetectsThemeType(bool $hyvaModule, ?string $path, string $expected): void
    {
        $helper = $this->helper($hyvaModule, $path);

        $this->assertSame($expected, $helper->getCurrentTheme());
        $this->assertSame($expected === Theme::THEME_HYVA, $helper->isHyva());
        $this->assertSame($expected === Theme::THEME_LUMA, $helper->isLuma());
        $this->assertSame($helper->isHyva(), $helper->useAlpineJs());
        $this->assertSame($helper->isLuma(), $helper->useKnockoutJs());
        $this->assertSame(
            $expected === Theme::THEME_HYVA ? 'h.phtml' : 'l.phtml',
            $helper->getTemplateForTheme('h.phtml', 'l.phtml')
        );
    }

    public function testDesignErrorFallsBackSafely(): void
    {
        $this->assertSame(Theme::THEME_HYVA, $this->helper(true, null, new \RuntimeException('x'))->getCurrentTheme());
        $this->assertSame(Theme::THEME_LUMA, $this->helper(false, null, new \RuntimeException('x'))->getCurrentTheme());
    }

    public function testResultIsCachedUntilReset(): void
    {
        $design = $this->createMock(DesignInterface::class);
        $theme = $this->createStub(ThemeInterface::class);
        $theme->method('getThemePath')->willReturnOnConsecutiveCalls('Magento/luma', 'Panth/Infotech');
        $design->expects($this->exactly(2))->method('getDesignTheme')->willReturn($theme);
        $moduleManager = $this->createStub(ModuleManager::class);
        $moduleManager->method('isEnabled')->willReturn(true);
        $helper = new Theme($this->createStub(Context::class), $design, $moduleManager);

        $this->assertTrue($helper->isLuma());
        $this->assertTrue($helper->isLuma());
        $helper->resetCache();
        $this->assertTrue($helper->isHyva());
    }
}
