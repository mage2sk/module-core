<?php
/**
 * Universal Config Save Observer (Disabled)
 * Webhook notifications removed
 *
 * @category  Panth
 * @package   Panth_Core
 */
declare(strict_types=1);

namespace Panth\Core\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class UniversalConfigSave implements ObserverInterface
{
    /**
     * Execute observer (no-op - webhook removed)
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        // Webhook notification removed
    }
}
