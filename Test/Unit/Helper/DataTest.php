<?php
declare(strict_types=1);

namespace Panth\Core\Test\Unit\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Panth\Core\Helper\Data;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class DataTest extends TestCase
{
    private ScopeConfigInterface $scopeConfig;
    private LoggerInterface $logger;
    private Data $helper;

    protected function setUp(): void
    {
        $this->scopeConfig = $this->createStub(ScopeConfigInterface::class);
        $this->helper = $this->helperWith($this->createStub(LoggerInterface::class));
    }

    private function helperWith(LoggerInterface $logger): Data
    {
        $this->logger = $logger;
        $context = $this->createStub(Context::class);
        $context->method('getScopeConfig')->willReturn($this->scopeConfig);
        $context->method('getLogger')->willReturn($logger);
        return new Data($context);
    }

    public function testFlagsReadStoreScopedConfig(): void
    {
        $this->scopeConfig->method('getValue')->willReturnMap([
            [Data::XML_PATH_ENABLED, ScopeInterface::SCOPE_STORE, 3, '1'],
            [Data::XML_PATH_DEBUG, ScopeInterface::SCOPE_STORE, 3, '0'],
            [Data::XML_PATH_CACHE, ScopeInterface::SCOPE_STORE, 3, '1'],
        ]);

        $this->assertTrue($this->helper->isEnabled(3));
        $this->assertFalse($this->helper->isDebugEnabled(3));
        $this->assertTrue($this->helper->isCacheEnabled(3));
    }

    public function testMissingValuesAreFalse(): void
    {
        $this->scopeConfig->method('getValue')->willReturn(null);

        $this->assertFalse($this->helper->isEnabled());
        $this->assertFalse($this->helper->isDebugEnabled());
        $this->assertFalse($this->helper->isCacheEnabled());
    }

    public function testLogWritesOnlyInDebugMode(): void
    {
        $this->scopeConfig->method('getValue')->willReturn('1');
        $this->helper = $this->helperWith($this->createMock(LoggerInterface::class));
        $this->logger->expects($this->once())->method('info')->with('Panth Core: hello', ['a' => 1]);

        $this->helper->log('hello', ['a' => 1]);
    }

    public function testLogIsSilentWhenDebugIsOff(): void
    {
        $this->scopeConfig->method('getValue')->willReturn('0');
        $this->helper = $this->helperWith($this->createMock(LoggerInterface::class));
        $this->logger->expects($this->never())->method('info');

        $this->helper->log('hello');
    }
}
