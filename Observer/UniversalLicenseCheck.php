<?php
/**
 * Universal License Check Observer (Disabled)
 * License validation removed
 *
 * @category  Panth
 * @package   Panth_Core
 */
declare(strict_types=1);

namespace Panth\Core\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class UniversalLicenseCheck implements ObserverInterface
{
    /**
     * Execute observer (no-op - license check removed)
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        // License validation removed
    }
}
