<?php
/**
 * Webhook Notifier Service (Disabled)
 * All webhook notifications have been removed
 *
 * @category  Panth
 * @package   Panth_Core
 */
declare(strict_types=1);

namespace Panth\Core\Service;

class WebhookNotifier
{
    /**
     * Send webhook notification (no-op)
     *
     * @param array $data
     * @return bool
     */
    public function sendNotification(array $data)
    {
        return true;
    }

    /**
     * Send notification for license bypass attempt (no-op)
     *
     * @param string $moduleName
     * @param string $bypassMethod
     * @param array $additionalData
     * @return void
     */
    public function notifyBypassAttempt($moduleName, $bypassMethod, array $additionalData = [])
    {
        // No-op
    }

    /**
     * Send notification for module usage without license (no-op)
     *
     * @param string $moduleName
     * @param string $accessPoint
     * @return void
     */
    public function notifyUnlicensedUsage($moduleName, $accessPoint)
    {
        // No-op
    }

    /**
     * Send notification for failed license validation (no-op)
     *
     * @param string $moduleName
     * @param string $reason
     * @return void
     */
    public function notifyValidationFailure($moduleName, $reason)
    {
        // No-op
    }
}
