<?php
declare(strict_types=1);

namespace Panth\Core\Test\Unit\Model\ChildTheme;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\DataObject;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Theme\Model\ResourceModel\Theme\Collection as ThemeCollection;
use Magento\Theme\Model\ResourceModel\Theme\CollectionFactory as ThemeCollectionFactory;
use Panth\Core\Model\ChildTheme\Validator;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    private string $appDir;
    private array $config = [];
    private bool $hyvaEnabled = true;

    protected function setUp(): void
    {
        $this->appDir = sys_get_temp_dir() . '/panth_core_validator_' . uniqid() . '/';
        mkdir($this->appDir, 0777, true);
        $this->config = ['design/theme/theme_id' => '7'];
    }

    protected function tearDown(): void
    {
        $this->removeDir($this->appDir);
    }

    private function removeDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        foreach (scandir($dir) as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = rtrim($dir, '/') . '/' . $item;
            is_dir($path) ? $this->removeDir($path) : unlink($path);
        }
        rmdir($dir);
    }

    private function writeFile(string $relative, string $content): void
    {
        $path = $this->appDir . $relative;
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }
        file_put_contents($path, $content);
    }

    private function theme(string $path, ?ThemeInterface $parent = null): ThemeInterface
    {
        $theme = $this->createStub(ThemeInterface::class);
        $theme->method('getThemePath')->willReturn($path);
        $theme->method('getParentTheme')->willReturn($parent);
        $theme->method('getId')->willReturn(7);
        return $theme;
    }

    private function validator($activeTheme): Validator
    {
        $moduleManager = $this->createStub(ModuleManager::class);
        $moduleManager->method('isEnabled')->willReturnCallback(fn ($m) => $m === 'Hyva_Theme' && $this->hyvaEnabled);

        $read = $this->createStub(ReadInterface::class);
        $read->method('getAbsolutePath')->willReturn($this->appDir);
        $filesystem = $this->createStub(Filesystem::class);
        $filesystem->method('getDirectoryRead')->willReturnCallback(fn ($code) => $code === DirectoryList::APP ? $read : null);

        $collection = $this->createStub(ThemeCollection::class);
        $collection->method('addFieldToFilter')->willReturnSelf();
        $collection->method('getFirstItem')->willReturn($activeTheme ?? new DataObject());
        $factory = $this->createStub(ThemeCollectionFactory::class);
        $factory->method('create')->willReturn($collection);

        $scopeConfig = $this->createStub(ScopeConfigInterface::class);
        $scopeConfig->method('getValue')->willReturnCallback(fn ($path) => $this->config[$path] ?? null);

        return new Validator($moduleManager, $filesystem, $factory, $scopeConfig);
    }

    private function checksByLabel(array $result): array
    {
        $out = [];
        foreach ($result['checks'] as $check) {
            $out[$check['label']] = $check;
        }
        return $out;
    }

    public function testPanthInfotechOnHyvaPassesAndSkipsChildOnlyChecks(): void
    {
        $theme = $this->theme('Panth/Infotech', $this->theme('Hyva/default'));
        $result = $this->validator($theme)->runAllChecks();
        $checks = $this->checksByLabel($result);

        $this->assertSame([
            'active_theme' => 'Panth/Infotech',
            'parent_chain' => 'Panth/Infotech -> Hyva/default',
            'hyva_detected' => true,
            'is_child_theme' => false,
        ], $result['theme_info']);
        $this->assertCount(6, $result['checks']);
        $this->assertSame('pass', $checks['Hyva_Theme Module']['status']);
        $this->assertSame('pass', $checks['Theme Parent Chain']['status']);
        $this->assertSame('info', $checks['Tailwind Source Config']['status']);
        $this->assertSame('info', $checks['CSS File Size']['status']);
        $this->assertSame('pass', $checks['CSS Merge/Minify']['status']);
        $this->assertSame('info', $checks['view.xml Configuration']['status']);
    }

    public function testPanthInfotechWithoutHyvaParentFails(): void
    {
        $this->hyvaEnabled = false;
        $checks = $this->checksByLabel($this->validator($this->theme('Panth/Infotech'))->runAllChecks());

        $this->assertSame('fail', $checks['Hyva_Theme Module']['status']);
        $this->assertSame('fail', $checks['Theme Parent Chain']['status']);
        $this->assertStringContainsString('missing Hyva/default', $checks['Theme Parent Chain']['message']);
    }

    public function testMissingThemeIdReportsUnknownTheme(): void
    {
        $this->config = [];
        $result = $this->validator(null)->runAllChecks();
        $checks = $this->checksByLabel($result);

        $this->assertSame('Unknown', $result['theme_info']['active_theme']);
        $this->assertSame('', $result['theme_info']['parent_chain']);
        $this->assertFalse($result['theme_info']['is_child_theme']);
        $this->assertSame('fail', $checks['Theme Parent Chain']['status']);
        $this->assertSame('info', $checks['Tailwind Source Config']['status']);
    }

    public function testHealthyChildThemePassesEveryCheck(): void
    {
        $theme = $this->theme('Vendor/Child', $this->theme('Panth/Infotech', $this->theme('Hyva/default')));
        $this->writeFile('design/frontend/Vendor/Child/web/tailwind/tailwind-source.css', '@source "../../../../Panth/Infotech/**/*.phtml";');
        $this->writeFile('design/frontend/Vendor/Child/web/css/styles.css', str_repeat('a', 9000));
        $this->writeFile('design/frontend/Panth/Infotech/web/css/styles.css', str_repeat('a', 10000));
        $this->writeFile('design/frontend/Vendor/Child/etc/view.xml', '<view/>');
        $this->config['dev/css/minify_files'] = '1';

        $result = $this->validator($theme)->runAllChecks();
        $checks = $this->checksByLabel($result);

        $this->assertTrue($result['theme_info']['is_child_theme']);
        $this->assertSame('Vendor/Child -> Panth/Infotech -> Hyva/default', $result['theme_info']['parent_chain']);
        foreach ($checks as $label => $check) {
            $this->assertSame('pass', $check['status'], $label);
        }
        $this->assertStringContainsString('90% ratio', $checks['CSS File Size']['message']);
        $this->assertStringContainsString('minification is enabled', $checks['CSS Merge/Minify']['message']);
    }

    public function testBrokenChildThemeReportsEachProblem(): void
    {
        $theme = $this->theme('Vendor/Child', $this->theme('Magento/blank'));
        $this->writeFile('design/frontend/Vendor/Child/web/tailwind/tailwind-source.css', '@import "tailwindcss";');
        $this->writeFile('design/frontend/Vendor/Child/web/css/styles.css', str_repeat('a', 2048));
        $this->writeFile('design/frontend/Panth/Infotech/web/css/styles.css', str_repeat('a', 2 * 1048576));
        $this->config['dev/css/merge_css_files'] = '1';

        $result = $this->validator($theme)->runAllChecks();
        $checks = $this->checksByLabel($result);

        $this->assertFalse($result['theme_info']['is_child_theme']);
        $this->assertSame('fail', $checks['Theme Parent Chain']['status']);
        $this->assertStringContainsString('Panth/Infotech is missing', $checks['Theme Parent Chain']['message']);
        $this->assertStringContainsString('Hyva/default is missing', $checks['Theme Parent Chain']['message']);
        $this->assertSame('fail', $checks['Tailwind Source Config']['status']);
        $this->assertSame('fail', $checks['CSS File Size']['status']);
        $this->assertStringContainsString('2 KB', $checks['CSS File Size']['message']);
        $this->assertStringContainsString('2 MB', $checks['CSS File Size']['message']);
        $this->assertSame('fail', $checks['CSS Merge/Minify']['status']);
        $this->assertSame('warning', $checks['view.xml Configuration']['status']);
    }

    public function testChildThemeWithoutBuiltFiles(): void
    {
        $theme = $this->theme('Vendor/Child', $this->theme('Panth/Infotech', $this->theme('Hyva/default')));

        $checks = $this->checksByLabel($this->validator($theme)->runAllChecks());

        $this->assertSame('fail', $checks['Tailwind Source Config']['status']);
        $this->assertStringContainsString('not found', $checks['Tailwind Source Config']['message']);
        $this->assertSame('fail', $checks['CSS File Size']['status']);
        $this->assertStringContainsString('npm run build', $checks['CSS File Size']['message']);

        $this->writeFile('design/frontend/Vendor/Child/web/css/styles.css', 'a');
        $checks = $this->checksByLabel($this->validator($theme)->runAllChecks());
        $this->assertSame('warning', $checks['CSS File Size']['status']);
    }

    public function testCollectionErrorsDegradeToUnknownTheme(): void
    {
        $factory = $this->createStub(ThemeCollectionFactory::class);
        $factory->method('create')->willThrowException(new \RuntimeException('db down'));
        $scopeConfig = $this->createStub(ScopeConfigInterface::class);
        $scopeConfig->method('getValue')->willReturn('6');
        $validator = new Validator(
            $this->createStub(ModuleManager::class),
            $this->createStub(Filesystem::class),
            $factory,
            $scopeConfig
        );

        $result = $validator->runAllChecks();

        $this->assertSame('Unknown', $result['theme_info']['active_theme']);
        $this->assertCount(6, $result['checks']);
    }
}
