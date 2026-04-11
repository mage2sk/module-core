<?php
/**
 * Panth Installation Tracker Service (Disabled)
 * Tracking and phone-home functionality removed
 *
 * @category  Panth
 * @package   Panth_Core
 */
declare(strict_types=1);

namespace Panth\Core\Service;

class InstallationTracker
{
    /**
     * Send installation notification (no-op)
     *
     * @param string $moduleName
     * @param string $eventType
     * @return bool
     */
    public function sendNotification($moduleName, $eventType = 'install')
    {
        return true;
    }

    /**
     * Send module list update (no-op)
     *
     * @return bool
     */
    public function sendModuleInventory()
    {
        return true;
    }
}
