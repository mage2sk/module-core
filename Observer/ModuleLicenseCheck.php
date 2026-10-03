<?php
declare(strict_types=1);

namespace Panth\Core\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

abstract class ModuleLicenseCheck implements ObserverInterface
{
    public function execute(Observer $observer)
    {
    }

    abstract protected function getModuleName();
}
