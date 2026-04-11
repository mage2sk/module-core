<?php
/**
 * Base Module Config Save Observer (Disabled)
 * Webhook notifications removed
 *
 * @category  Panth
 * @package   Panth_Core
 */
declare(strict_types=1);

namespace Panth\Core\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

abstract class ModuleConfigSaveObserver implements ObserverInterface
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

    /**
     * Get module name
     *
     * @return string
     */
    abstract protected function getModuleName();

    /**
     * Get config section ID
     *
     * @return string
     */
    abstract protected function getConfigSection();
}
