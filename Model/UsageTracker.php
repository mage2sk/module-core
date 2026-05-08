<?php
/**
 * Panth Core — Usage Tracker (deprecated stub).
 *
 * The Telegram-bot integration that previously lived here was removed in
 * Core 1.0.14. Aggregate reporting now flows through the receiver-side
 * Panth_NotificationsPublisher (https://kishansavaliya.com), and each
 * individual Panth module ships its own InstallReporter so install /
 * upgrade / heartbeat events are visible per-module without depending
 * on Core at all.
 *
 * The class is kept as a no-op for backward compatibility — older data
 * patches still call sendNotification() and we don't want them throwing
 * "class not found" on existing installs that haven't pulled the latest
 * patches yet. New code should NOT call this class.
 *
 * @category  Panth
 * @package   Panth_Core
 */
declare(strict_types=1);

namespace Panth\Core\Model;

use Psr\Log\LoggerInterface;

class UsageTracker
{
    public function __construct(
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * No-op. Retained for backward compatibility with legacy data patches.
     *
     * @param string $event
     */
    public function sendNotification(string $event): void
    {
        $this->logger->debug('Panth\Core\UsageTracker::sendNotification is a no-op since 1.0.14: ' . $event);
    }
}
