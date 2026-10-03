<?php
declare(strict_types=1);

namespace Panth\Core\Test\Unit\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Panth\Core\Observer\ModuleConfigSaveObserver;
use Panth\Core\Observer\ModuleLicenseCheck;
use PHPUnit\Framework\TestCase;

class AbstractObserversTest extends TestCase
{
    public function testBaseObserversAreSideEffectFreeForSubclasses(): void
    {
        $observer = $this->createMock(Observer::class);
        $observer->expects($this->never())->method($this->anything());

        $configSave = new class extends ModuleConfigSaveObserver {
            protected function getModuleName()
            {
                return 'Panth_Sample';
            }

            protected function getConfigSection()
            {
                return 'sample_section';
            }
        };
        $licenseCheck = new class extends ModuleLicenseCheck {
            protected function getModuleName()
            {
                return 'Panth_Sample';
            }
        };

        $this->assertInstanceOf(ObserverInterface::class, $configSave);
        $this->assertInstanceOf(ObserverInterface::class, $licenseCheck);
        $this->assertNull($configSave->execute($observer));
        $this->assertNull($licenseCheck->execute($observer));
    }
}
