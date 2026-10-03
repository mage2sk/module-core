<?php
declare(strict_types=1);

namespace Panth\Core\Test\Unit\Model;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\Module\Dir;
use Magento\Framework\Module\Dir\Reader as ModuleDirReader;
use Magento\Framework\Serialize\SerializerInterface;
use Panth\Core\Model\Config\Reader\ModuleRegistry as ModuleRegistryReader;
use Panth\Core\Model\Config\SchemaLocator\ModuleRegistry as SchemaLocator;
use Panth\Core\Model\ModuleRegistry;
use PHPUnit\Framework\TestCase;

class ModuleRegistryTest extends TestCase
{
    private const MODULES = [
        'Panth_Core' => ['name' => 'Panth_Core', 'config_section' => 'panth_core', 'enabled' => true],
        'Panth_Faq' => ['name' => 'Panth_Faq', 'config_section' => 'panth_faq', 'enabled' => false],
    ];

    public function testReadsFromReaderOnCacheMissAndStoresResult(): void
    {
        $reader = $this->createMock(ModuleRegistryReader::class);
        $reader->expects($this->once())->method('read')->willReturn(self::MODULES);
        $cache = $this->createMock(CacheInterface::class);
        $cache->expects($this->once())->method('load')->with(ModuleRegistry::CACHE_ID)->willReturn(false);
        $cache->expects($this->once())->method('save')->with('serialized', ModuleRegistry::CACHE_ID, ['config']);
        $serializer = $this->createStub(SerializerInterface::class);
        $serializer->method('serialize')->willReturnMap([[self::MODULES, 'serialized']]);

        $registry = new ModuleRegistry($reader, $cache, $serializer);

        $this->assertSame(self::MODULES, $registry->getModules());
        $this->assertSame(self::MODULES, $registry->getModules());
        $this->assertSame('panth_faq', $registry->getConfigSection('Panth_Faq'));
        $this->assertNull($registry->getConfigSection('Panth_Missing'));
        $this->assertTrue($registry->isRegistered('Panth_Core'));
        $this->assertFalse($registry->isRegistered('Panth_Missing'));
        $this->assertSame(['panth_core', 'panth_faq'], $registry->getAllConfigSections());
    }

    public function testUsesCachedCopyWithoutReadingXml(): void
    {
        $reader = $this->createMock(ModuleRegistryReader::class);
        $reader->expects($this->never())->method('read');
        $cache = $this->createMock(CacheInterface::class);
        $cache->method('load')->willReturn('cached');
        $cache->expects($this->never())->method('save');
        $serializer = $this->createStub(SerializerInterface::class);
        $serializer->method('unserialize')->willReturnMap([['cached', self::MODULES]]);

        $registry = new ModuleRegistry($reader, $cache, $serializer);

        $this->assertSame(['panth_core', 'panth_faq'], $registry->getAllConfigSections());
    }

    public function testSchemaLocatorPointsAtCoreXsd(): void
    {
        $dirReader = $this->createMock(ModuleDirReader::class);
        $dirReader->expects($this->once())->method('getModuleDir')
            ->with(Dir::MODULE_ETC_DIR, 'Panth_Core')
            ->willReturn('/app/code/Panth/Core/etc');

        $locator = new SchemaLocator($dirReader);

        $this->assertSame('/app/code/Panth/Core/etc/panth_modules.xsd', $locator->getSchema());
        $this->assertSame('/app/code/Panth/Core/etc/panth_modules.xsd', $locator->getPerFileSchema());
    }

    public function testShippedRegistryXmlValidatesAgainstXsd(): void
    {
        $etc = dirname(__DIR__, 3) . '/etc';
        $dom = new \DOMDocument();
        $dom->load($etc . '/panth_modules.xml');

        $this->assertTrue($dom->schemaValidate($etc . '/panth_modules.xsd'));
        $converted = (new \Panth\Core\Model\Config\Converter\ModuleRegistry())->convert($dom);
        $this->assertSame('panth_core', $converted['Panth_Core']['config_section']);
        $this->assertTrue($converted['Panth_Core']['enabled']);
    }
}
