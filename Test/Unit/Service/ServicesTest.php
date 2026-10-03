<?php
declare(strict_types=1);

namespace Panth\Core\Test\Unit\Service;

use Panth\Core\Service\DomainWhitelist;
use Panth\Core\Service\LicenseValidator;
use Panth\Core\Service\ValidationCache;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ServicesTest extends TestCase
{
    public function testDomainWhitelistApprovesEverything(): void
    {
        $whitelist = new DomainWhitelist();

        $this->assertTrue($whitelist->isCurrentDomainApproved());
        $this->assertTrue($whitelist->isDomainApproved('shop.example.com'));
        $this->assertTrue($whitelist->addDomain('shop.example.com'));
        $this->assertTrue($whitelist->removeDomain('shop.example.com'));
    }

    public function testLicenseValidatorIsPermissiveAndHasNoErrors(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->never())->method($this->anything());
        $validator = new LicenseValidator($logger);

        $this->assertTrue($validator->validate('Panth_Sample'));
        $this->assertSame('localhost', $validator->getDomain());
        $this->assertNull($validator->forceRevalidate('Panth_Sample'));
        $this->assertTrue($validator->checkIntegrity());
        $this->assertSame('', $validator->getLastError());
        $this->assertSame([], $validator->getLastLicenseData());
    }

    public function testValidationCacheAlwaysReportsValid(): void
    {
        $cache = new ValidationCache();
        $cache->store('Panth_Sample', 'key', false, ['x' => 1]);
        $cache->clear('Panth_Sample', 'key');
        $cache->clearAll();

        $this->assertTrue($cache->isValid('Panth_Sample', 'key'));
        $this->assertSame(
            ['module' => 'Panth_Sample', 'validated' => true, 'license_data' => []],
            $cache->get('Panth_Sample', 'key')
        );
    }
}
