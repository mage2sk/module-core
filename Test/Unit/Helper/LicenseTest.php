<?php
declare(strict_types=1);

namespace Panth\Core\Test\Unit\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Panth\Core\Helper\License;
use Panth\Core\Helper\ModuleLicenseValidator;
use Panth\Core\Service\LicenseValidator;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class LicenseTest extends TestCase
{
    public function testLicenseHelperIsAlwaysActiveAndReportsDomain(): void
    {
        $validator = $this->createStub(LicenseValidator::class);
        $validator->method('getDomain')->willReturn('store.example.com');
        $helper = new License($this->createStub(Context::class), $validator);

        $this->assertSame('valid', $helper->getLicenseKey());
        $this->assertTrue($helper->validateLicense());
        $this->assertTrue($helper->validateLicense(true));
        $this->assertSame('', $helper->getError());
        $this->assertTrue($helper->isLicenseActive());
        $this->assertTrue($helper->deactivateLicense());
        $this->assertSame([
            'productName' => 'Panth Suite',
            'licenseType' => 'Unlimited',
            'isValid' => true,
            'domain' => 'store.example.com',
        ], $helper->getLicenseData());
    }

    public function testModuleLicenseValidatorUsesConfigPathOfSubclass(): void
    {
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->expects($this->exactly(2))->method('getValue')
            ->with('sample_section/general/enabled', ScopeInterface::SCOPE_STORE)
            ->willReturnOnConsecutiveCalls('1', '0');
        $context = $this->createStub(Context::class);
        $context->method('getScopeConfig')->willReturn($scopeConfig);

        $helper = new class(
            $context,
            $this->createStub(LicenseValidator::class),
            $this->createStub(LoggerInterface::class)
        ) extends ModuleLicenseValidator {
            protected function getModuleName()
            {
                return 'Panth_Sample';
            }

            protected function getConfigPath()
            {
                return 'sample_section';
            }
        };

        $this->assertTrue($helper->isModuleEnabled());
        $this->assertFalse($helper->isModuleEnabled());
        $this->assertTrue($helper->validateLicense());
        $this->assertTrue($helper->validateAndNotify('save'));
    }
}
