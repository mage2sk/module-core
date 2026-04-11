<?php
/**
 * Webhook Helper (Disabled)
 * Webhook notifications removed
 *
 * @category  Panth
 * @package   Panth_Core
 */
declare(strict_types=1);

namespace Panth\Core\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class Webhook extends AbstractHelper
{
    /**
     * @param Context $context
     */
    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * Send webhook notification (no-op)
     *
     * @param string $event
     * @param array $data
     * @return bool
     */
    public function sendNotification(string $event, array $data = []): bool
    {
        return true;
    }

    /**
     * Send config change notification (no-op)
     *
     * @param string $moduleName
     * @param array $changedPaths
     * @return bool
     */
    public function sendConfigChangeNotification(string $moduleName, array $changedPaths = []): bool
    {
        return true;
    }

    /**
     * Notify config change (no-op)
     *
     * @param string $section
     * @param string $moduleName
     * @return bool
     */
    public function notifyConfigChange(string $section, string $moduleName): bool
    {
        return true;
    }
}
