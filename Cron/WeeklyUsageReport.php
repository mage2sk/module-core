<?php
declare(strict_types=1);

namespace Panth\Core\Cron;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Panth\Core\Model\UsageTracker;

/**
 * Sends a weekly usage report if tracking is opted-in.
 */
class WeeklyUsageReport
{
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly UsageTracker $usageTracker
    ) {
    }

    public function execute(): void
    {
        if (!$this->scopeConfig->isSetFlag('panth_core/usage_tracking/enabled')) {
            return;
        }

        $this->usageTracker->sendNotification('Weekly Status Report');
    }
}
