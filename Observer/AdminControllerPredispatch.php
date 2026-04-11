<?php
/**
 * Admin Controller Predispatch Observer (Disabled)
 * License validation removed - all admin actions are allowed
 *
 * @category  Panth
 * @package   Panth_Core
 */
declare(strict_types=1);

namespace Panth\Core\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class AdminControllerPredispatch implements ObserverInterface
{
    /**
     * Execute observer (no-op - license check removed)
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        // License validation removed - all admin actions allowed
    }
}
