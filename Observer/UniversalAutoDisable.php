<?php
/**
 * Universal Auto-Disable Observer (Disabled)
 * Auto-disable functionality removed
 *
 * @category  Panth
 * @package   Panth_Core
 */
declare(strict_types=1);

namespace Panth\Core\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class UniversalAutoDisable implements ObserverInterface
{
    /**
     * Execute observer (no-op - auto-disable removed)
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        // Auto-disable functionality removed
    }
}
