<?php
declare(strict_types=1);

namespace Panth\Core\Test\Unit\Block\Adminhtml\System\Config;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Escaper;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\UrlInterface;
use Panth\Core\Block\Adminhtml\System\Config\ChildThemeGuide;
use Panth\Core\Block\Adminhtml\System\Config\ChildThemeValidation;
use Panth\Core\Block\Adminhtml\System\Config\LicenseInfo;
use Panth\Core\Block\Adminhtml\System\Config\LicenseStatus;
use Panth\Core\Block\Adminhtml\System\Config\ModuleVersion;
use Panth\Core\Model\ChildTheme\Validator;
use Panth\Core\Model\Config\Source\ColorPicker;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

class ConfigFieldsTest extends TestCase
{
    private function setProperty(object $object, string $class, string $name, $value): void
    {
        $property = new \ReflectionProperty($class, $name);
        $property->setValue($object, $value);
    }

    private function callProtected(object $object, string $method, ...$args)
    {
        $reflection = new \ReflectionMethod($object, $method);
        return $reflection->invoke($object, ...$args);
    }

    private function escaper(): Escaper
    {
        $escaper = $this->createStub(Escaper::class);
        $escape = fn ($value) => htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $escaper->method('escapeHtml')->willReturnCallback($escape);
        $escaper->method('escapeHtmlAttr')->willReturnCallback($escape);
        return $escaper;
    }

    private function moduleVersion(?string $path, ?string $composer, bool $throws = false): ModuleVersion
    {
        $registrar = $this->createStub(ComponentRegistrarInterface::class);
        $registrar->method('getPath')->willReturnMap([[ComponentRegistrar::MODULE, 'Panth_Core', $path]]);
        $directory = $this->createStub(ReadInterface::class);
        $directory->method('isFile')->willReturnMap([['composer.json', $composer !== null]]);
        if ($throws) {
            $directory->method('readFile')->willThrowException(new \RuntimeException('unreadable'));
        } else {
            $directory->method('readFile')->willReturn((string) $composer);
        }
        $readFactory = $this->createStub(ReadFactory::class);
        $readFactory->method('create')->willReturn($directory);

        $block = (new \ReflectionClass(ModuleVersion::class))->newInstanceWithoutConstructor();
        $this->setProperty($block, ModuleVersion::class, 'componentRegistrar', $registrar);
        $this->setProperty($block, ModuleVersion::class, 'readFactory', $readFactory);
        $this->setProperty($block, ModuleVersion::class, 'json', new Json());
        $this->setProperty($block, \Magento\Framework\View\Element\AbstractBlock::class, '_escaper', $this->escaper());
        return $block;
    }

    public function testModuleVersionReadsComposerJson(): void
    {
        $block = $this->moduleVersion('/vendor/mage2kishan/module-core', '{"version":"1.2.5"}');

        $this->assertSame('1.2.5', $block->getModuleVersion());
        $this->assertSame(
            '<span>Panth Core v1.2.5</span>',
            $this->callProtected($block, '_getElementHtml', $this->createStub(AbstractElement::class))
        );
    }

    public function testModuleVersionFallsBackWhenUnavailable(): void
    {
        $element = $this->createStub(AbstractElement::class);
        foreach ([
            $this->moduleVersion(null, null),
            $this->moduleVersion('/x', null),
            $this->moduleVersion('/x', '{"name":"mage2kishan/module-core"}'),
            $this->moduleVersion('/x', '{}', true),
        ] as $block) {
            $this->assertSame('', $block->getModuleVersion());
            $this->assertSame('<span>Panth Core</span>', $this->callProtected($block, '_getElementHtml', $element));
        }
    }

    public function testValidationBlockDelegatesAndBuildsUrls(): void
    {
        $validator = $this->createStub(Validator::class);
        $validator->method('runAllChecks')->willReturn(['checks' => [1]]);
        $url = $this->createStub(UrlInterface::class);
        $url->method('getUrl')->willReturnCallback(fn ($route) => 'https://admin.example.com/' . $route);

        $block = (new \ReflectionClass(ChildThemeValidation::class))->newInstanceWithoutConstructor();
        $this->setProperty($block, ChildThemeValidation::class, 'themeValidator', $validator);
        $this->setProperty($block, \Magento\Framework\View\Element\AbstractBlock::class, '_urlBuilder', $url);

        $this->assertSame(['checks' => [1]], $block->getValidationResults());
        $this->assertSame('https://admin.example.com/panthcore/childtheme/validate', $block->getValidateUrl());
        $this->assertSame('https://admin.example.com/panthcore/childtheme/rebuild', $block->getRebuildUrl());
        $this->assertSame('', $this->callProtected($block, '_renderScopeLabel', $this->createStub(AbstractElement::class)));
        $this->assertSame('', $this->callProtected($block, '_renderInheritCheckbox', $this->createStub(AbstractElement::class)));
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testGuideRendersFullWidthRow(): void
    {
        $guide = $this->getMockBuilder(ChildThemeGuide::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['_toHtml'])
            ->getMock();
        $guide->method('_toHtml')->willReturn('<div class="panth-guide">Guide</div>');
        $this->setProperty($guide, \Magento\Framework\View\Element\AbstractBlock::class, '_escaper', $this->escaper());
        $element = $this->getMockBuilder(AbstractElement::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getHtmlId'])
            ->getMock();
        $element->method('getHtmlId')->willReturn('panth_core_child_theme_guide_guide_content');

        $html = $guide->render($element);

        $this->assertSame(
            '<tr id="row_panth_core_child_theme_guide_guide_content"><td colspan="4">'
            . '<div class="panth-guide">Guide</div></td></tr>',
            $html
        );
        $this->assertSame('<div class="panth-guide">Guide</div>', $this->callProtected($guide, '_getElementHtml', $element));
        $this->assertSame('', $this->callProtected($guide, '_renderScopeLabel', $element));
        $this->assertSame('', $this->callProtected($guide, '_renderInheritCheckbox', $element));
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testValidationPanelRendersFullWidthRow(): void
    {
        $block = $this->getMockBuilder(ChildThemeValidation::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['_toHtml'])
            ->getMock();
        $block->method('_toHtml')->willReturn('<div class="panth-validation">Panel</div>');
        $this->setProperty($block, \Magento\Framework\View\Element\AbstractBlock::class, '_escaper', $this->escaper());
        $element = $this->getMockBuilder(AbstractElement::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getHtmlId'])
            ->getMock();
        $element->method('getHtmlId')->willReturn('panth_core_child_theme_validation_validation_panel');

        $this->assertSame(
            '<tr id="row_panth_core_child_theme_validation_validation_panel"><td colspan="4">'
            . '<div class="panth-validation">Panel</div></td></tr>',
            $block->render($element)
        );
        $this->assertSame('<div class="panth-validation">Panel</div>', $this->callProtected($block, '_getElementHtml', $element));
    }

    public function testLicenseFieldsRenderStaticMarkup(): void
    {
        $element = $this->createStub(AbstractElement::class);
        $info = (new \ReflectionClass(LicenseInfo::class))->newInstanceWithoutConstructor();
        $status = (new \ReflectionClass(LicenseStatus::class))->newInstanceWithoutConstructor();

        $this->assertSame('', $this->callProtected($info, '_getElementHtml', $element));
        $this->assertStringContainsString('License Active', $this->callProtected($status, '_getElementHtml', $element));
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testColorPickerEscapesIdAndDefaultsToBlack(): void
    {
        $element = $this->getMockBuilder(AbstractElement::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getElementHtml', 'getHtmlId', 'getData'])
            ->getMock();
        $element->method('getElementHtml')->willReturn('<input id="x"/>');
        $element->method('getHtmlId')->willReturn('a"b');
        $element->method('getData')->willReturn(null);
        $picker = (new \ReflectionClass(ColorPicker::class))->newInstanceWithoutConstructor();

        $html = $this->callProtected($picker, '_getElementHtml', $element);

        $this->assertStringStartsWith('<input id="x"/>', $html);
        $this->assertStringContainsString('$("#a\\"b")', $html);
        $this->assertStringContainsString('"#000000"', $html);
        $this->assertStringContainsString('type=\\"color\\"', $html);
    }
}
