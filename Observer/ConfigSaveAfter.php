<?php
/**
 * Panth Core Config Save Observer (Disabled)
 * Tracking notifications removed
 *
 * @category  Panth
 * @package   Panth_Core
 */
declare(strict_types=1);

namespace Panth\Core\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ConfigSaveAfter implements ObserverInterface
{
    /**
     * Execute observer (no-op - tracking removed)
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        // Installation tracking removed
    }
}
