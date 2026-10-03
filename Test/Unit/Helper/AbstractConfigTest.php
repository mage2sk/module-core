<?php
declare(strict_types=1);

namespace Panth\Core\Test\Unit\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Panth\Core\Helper\AbstractConfig;
use PHPUnit\Framework\TestCase;

class AbstractConfigTest extends TestCase
{
    public function testSubclassReadsGroupFieldAndFlagsInStoreScope(): void
    {
        $scopeConfig = $this->createStub(ScopeConfigInterface::class);
        $scopeConfig->method('getValue')->willReturnMap([
            ['sample_section/general/title', ScopeInterface::SCOPE_STORE, 2, 'Sample'],
        ]);
        $scopeConfig->method('isSetFlag')->willReturnMap([
            ['sample_section/general/enabled', ScopeInterface::SCOPE_STORE, 2, true],
            ['sample_section/general/enabled', ScopeInterface::SCOPE_STORE, null, false],
        ]);
        $context = $this->createStub(Context::class);
        $context->method('getScopeConfig')->willReturn($scopeConfig);

        $helper = new class($context) extends AbstractConfig {
            protected function getConfigValue(string $group, string $field, $storeId = null)
            {
                return $this->getConfig('sample_section/' . $group . '/' . $field, $storeId);
            }

            public function isEnabled($storeId = null): bool
            {
                return $this->isSetFlag('sample_section/general/enabled', $storeId);
            }

            public function title($storeId = null)
            {
                return $this->getConfigValue('general', 'title', $storeId);
            }
        };

        $this->assertSame('Sample', $helper->title(2));
        $this->assertNull($helper->title(5));
        $this->assertTrue($helper->isEnabled(2));
        $this->assertFalse($helper->isEnabled());
    }
}
