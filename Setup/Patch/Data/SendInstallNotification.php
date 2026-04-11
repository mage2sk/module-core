<?php
declare(strict_types=1);

namespace Panth\Core\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Panth\Core\Model\UsageTracker;

/**
 * Sends a one-time "Module Installed" notification on first setup:upgrade.
 * This runs regardless of the opt-in toggle — it's a one-time install event only.
 */
class SendInstallNotification implements DataPatchInterface
{
    public function __construct(
        private readonly UsageTracker $usageTracker
    ) {
    }

    public function apply(): self
    {
        try {
            $this->usageTracker->sendNotification('Panth Core Module Installed (setup:upgrade)');
        } catch (\Throwable) {
            // Never block setup:upgrade
        }

        return $this;
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }
}
