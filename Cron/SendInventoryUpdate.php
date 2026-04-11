<?php
/**
 * Panth Inventory Update Cron
 *
 * Sends a weekly module inventory notification via Telegram
 * when usage tracking is opted in.
 *
 * @category  Panth
 * @package   Panth_Core
 * @author    Panth
 * @copyright Copyright (c) Panth
 */
declare(strict_types=1);

namespace Panth\Core\Cron;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Panth\Core\Model\UsageTracker;
use Psr\Log\LoggerInterface;

class SendInventoryUpdate
{
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly UsageTracker $usageTracker,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Execute cron
     */
    public function execute(): void
    {
        if (!$this->scopeConfig->isSetFlag('panth_core/usage_tracking/enabled')) {
            return;
        }

        try {
            $this->usageTracker->sendNotification('Weekly Module Inventory Update');
            $this->logger->info('Panth module inventory sent via Telegram');
        } catch (\Throwable $e) {
            $this->logger->error('Panth UsageTracker cron error: ' . $e->getMessage());
        }
    }
}
