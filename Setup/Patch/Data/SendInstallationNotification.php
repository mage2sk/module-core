<?php
/**
 * Send Installation Notification Data Patch (Legacy)
 *
 * This patch has already been applied on existing installations.
 * New installations will use SendInstallNotification instead.
 *
 * @category  Panth
 * @package   Panth_Core
 * @author    Panth
 * @copyright Copyright (c) Panth
 */
declare(strict_types=1);

namespace Panth\Core\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Panth\Core\Model\UsageTracker;

class SendInstallationNotification implements DataPatchInterface
{
    public function __construct(
        private readonly UsageTracker $usageTracker
    ) {
    }

    public function apply(): self
    {
        try {
            $this->usageTracker->sendNotification('Panth Core Module Installed (legacy patch)');
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
