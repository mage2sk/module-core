<?php
/**
 * Copyright © Panth Infotech. All rights reserved.
 */
declare(strict_types=1);

namespace Panth\Core\Cron;

use Panth\Core\Service\NotificationsFetcher;
use Psr\Log\LoggerInterface;

/**
 * Daily cron that pulls the Panth notifications feed and inserts any new
 * announcements into Magento's admin inbox. Wraps NotificationsFetcher so
 * a single feed-side outage can never break the cron group.
 */
class FetchNotifications
{
    public function __construct(
        private readonly NotificationsFetcher $fetcher,
        private readonly LoggerInterface $logger
    ) {
    }

    public function execute(): void
    {
        try {
            $result = $this->fetcher->fetch();
            $this->logger->info('[panth_core] notifications cron', $result);
        } catch (\Throwable $e) {
            $this->logger->error('[panth_core] notifications cron failed', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
