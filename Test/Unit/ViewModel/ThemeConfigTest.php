<?php
declare(strict_types=1);

namespace Panth\Core\Test\Unit\ViewModel;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Module\Dir;
use Magento\Framework\Module\Dir\Reader as ModuleDirReader;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Store\Model\ScopeInterface;
use Panth\Core\ViewModel\Config;
use Panth\Core\ViewModel\ThemeConfig;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ThemeConfigTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = sys_get_temp_dir() . '/panth_core_themeconfig_' . uniqid();
        mkdir($this->root . '/ModA/etc', 0777, true);
        mkdir($this->root . '/ModB/etc', 0777, true);
        mkdir($this->root . '/parent/web/tailwind', 0777, true);
        mkdir($this->root . '/child/web/tailwind', 0777, true);
        file_put_contents($this->root . '/ModA/etc/theme-config.json', json_encode([
            'colors' => ['primary' => '#111111', 'accent' => '#222222'],
            'radius' => '4px',
        ]));
        file_put_contents($this->root . '/ModB/etc/theme-config.json', json_encode([
            'colors' => ['accent' => '#333333'],
        ]));
        file_put_contents($this->root . '/parent/web/tailwind/theme-config.json', json_encode([
            'colors' => ['primary' => '#444444'],
            'font' => 'DM Sans',
        ]));
        file_put_contents($this->root . '/child/web/tailwind/theme-config.json', json_encode([
            'font' => 'Child Sans',
        ]));
    }

    protected function tearDown(): void
    {
        foreach (['ModA/etc', 'ModB/etc', 'parent/web/tailwind', 'child/web/tailwind'] as $dir) {
            $file = $this->root . '/' . $dir . '/theme-config.json';
            if (is_file($file)) {
                unlink($file);
            }
        }
        foreach (['ModA/etc', 'ModA', 'ModB/etc', 'ModB', 'parent/web/tailwind', 'parent/web', 'parent',
                     'child/web/tailwind', 'child/web', 'child', ''] as $dir) {
            $path = $this->root . '/' . $dir;
            if (is_dir($path) && count(scandir($path)) === 2) {
                rmdir($path);
            }
        }
    }

    private function theme(string $fullPath, ?ThemeInterface $parent): ThemeInterface
    {
        $theme = $this->createStub(ThemeInterface::class);
        $theme->method('getFullPath')->willReturn($fullPath);
        $theme->method('getParentTheme')->willReturn($parent);
        return $theme;
    }

    private function viewModel(array $modules, ?ThemeInterface $theme, ?LoggerInterface $logger = null): ThemeConfig
    {
        $dirReader = $this->createStub(ModuleDirReader::class);
        $dirReader->method('getModuleDir')->willReturnCallback(function ($type, $module) {
            $this->assertSame(Dir::MODULE_ETC_DIR, $type);
            if ($module === 'Broken_Module') {
                throw new \InvalidArgumentException('unknown module');
            }
            return $this->root . '/' . $module . '/etc';
        });
        $design = $this->createStub(DesignInterface::class);
        $design->method('getDesignTheme')->willReturn($theme);
        $registrar = $this->createStub(ComponentRegistrar::class);
        $registrar->method('getPath')->willReturnCallback(fn ($type, $code) => match ($code) {
            'frontend/Panth/Infotech' => $this->root . '/parent',
            'frontend/Vendor/Child' => $this->root . '/child',
            default => null,
        });

        return new ThemeConfig(
            $dirReader,
            new Json(),
            $logger ?? $this->createStub(LoggerInterface::class),
            $design,
            $registrar,
            $modules
        );
    }

    public function testMergesModulesThenThemeChainWithChildWinning(): void
    {
        $child = $this->theme('frontend/Vendor/Child', $this->theme('frontend/Panth/Infotech', null));
        $vm = $this->viewModel(['ModA', 'ModB', 'Missing'], $child);

        $this->assertSame(
            ":root {\n  --colors-primary: #444444;\n  --colors-accent: #333333;\n  --radius: 4px;\n  --font: Child Sans;\n}\n",
            $vm->getCssVariables()
        );
        $this->assertSame('#444444', $vm->getValue('colors.primary'));
        $this->assertSame('Child Sans', $vm->getValue('font'));
        $this->assertSame('x', $vm->getValue('colors.none', 'x'));
        $this->assertNull($vm->getValue('colors'));
    }

    public function testEmptyWhenNothingIsRegistered(): void
    {
        $vm = $this->viewModel([], $this->theme('frontend/Magento/luma', null));

        $this->assertSame('', $vm->getCssVariables());
        $this->assertSame('d', $vm->getValue('colors.primary', 'd'));
    }

    public function testBrokenModuleIsLoggedAndSkipped(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('warning');
        $vm = $this->viewModel(['Broken_Module', 'ModB'], null, $logger);

        $this->assertSame(":root {\n  --colors-accent: #333333;\n}\n", $vm->getCssVariables());
    }

    public function testConfigViewModelReadsStoreScope(): void
    {
        $scopeConfig = $this->createStub(ScopeConfigInterface::class);
        $scopeConfig->method('getValue')->willReturnMap([['a/b/c', ScopeInterface::SCOPE_STORE, 4, 'v']]);
        $scopeConfig->method('isSetFlag')->willReturnMap([['a/b/d', ScopeInterface::SCOPE_STORE, null, true]]);
        $config = new Config($scopeConfig);

        $this->assertSame('v', $config->getConfig('a/b/c', 4));
        $this->assertTrue($config->isSetFlag('a/b/d'));
    }
}
