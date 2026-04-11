<?php
/**
 * Panth Core - Usage Tracking Observer
 *
 * Listens for config changes on the panth_core section.
 * When usage tracking is enabled, sends a one-time notification
 * with anonymous site info to Panth Infotech.
 *
 * @category  Panth
 * @package   Panth_Core
 */
declare(strict_types=1);

namespace Panth\Core\Observer\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Panth\Core\Model\UsageTracker;

class UsageTrackingObserver implements ObserverInterface
{
    private const XML_PATH_TRACKING_ENABLED = 'panth_core/usage_tracking/enabled';

    private ScopeConfigInterface $scopeConfig;
    private UsageTracker $usageTracker;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        UsageTracker $usageTracker
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->usageTracker = $usageTracker;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        try {
            if (!$this->scopeConfig->isSetFlag(self::XML_PATH_TRACKING_ENABLED)) {
                return;
            }

            $this->usageTracker->sendNotification('Module tracking enabled');
        } catch (\Throwable $e) {
            // Never disrupt admin operations
        }
    }
}
