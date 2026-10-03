<?php
declare(strict_types=1);

namespace Panth\Core\Test\Unit\Model\Config\Converter;

use Panth\Core\Model\Config\Converter\ModuleRegistry;
use PHPUnit\Framework\TestCase;

class ModuleRegistryConverterTest extends TestCase
{
    public function testConvertsModuleNodesKeyedByName(): void
    {
        $document = new \DOMDocument();
        $document->loadXML(
            '<config><modules>'
            . '<module name="Acme_Slider" config_section="acme_slider"/>'
            . '<module name="Acme_Faq" config_section="acme_faq" enabled="0"/>'
            . '<module name="Acme_Badge" config_section="acme_badge" enabled="1"/>'
            . '</modules></config>'
        );

        $result = (new ModuleRegistry())->convert($document);

        $this->assertSame(['Acme_Slider', 'Acme_Faq', 'Acme_Badge'], array_keys($result));
        $this->assertSame(
            ['name' => 'Acme_Slider', 'config_section' => 'acme_slider', 'enabled' => true],
            $result['Acme_Slider']
        );
        $this->assertFalse($result['Acme_Faq']['enabled']);
        $this->assertTrue($result['Acme_Badge']['enabled']);
    }

    public function testEmptyRegistryConvertsToEmptyArray(): void
    {
        $document = new \DOMDocument();
        $document->loadXML('<config><modules/></config>');

        $this->assertSame([], (new ModuleRegistry())->convert($document));
    }
}
