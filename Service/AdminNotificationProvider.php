<?php
/**
 * Copyright © Panth Infotech. All rights reserved.
 */
declare(strict_types=1);

namespace Panth\Core\Service;

use Magento\Backend\Model\Auth\Session as BackendAuthSession;
use Magento\Framework\App\ResourceConnection;
use Panth\Core\Model\AdminNotification;
use Panth\Core\Model\AdminNotificationDisplayFactory;
use Panth\Core\Model\ResourceModel\AdminNotification\CollectionFactory as NotificationCollectionFactory;
use Panth\Core\Model\ResourceModel\AdminNotificationDisplay as DisplayResource;
use Panth\Core\Model\ResourceModel\AdminNotificationDisplay\CollectionFactory as DisplayCollectionFactory;
use Psr\Log\LoggerInterface;

/**
 * Decides which messages get rendered to a given admin user as a popup
 * or a top-bar banner. Reads the local mirror table populated by
 * NotificationsFetcher and joins per-admin-user state from the display
 * table.
 *
 * "Show on login a few times" rule:
 *   - default cap is MAX_POPUP_SHOWS shows per admin
 *   - clicking dismiss sets dismissed_at and stops further shows
 *   - clicking the CTA also counts as a dismiss
 */
class AdminNotificationProvider
{
    public const MAX_POPUP_SHOWS = 3;

    public function __construct(
        private readonly NotificationCollectionFactory $notificationCollectionFactory,
        private readonly DisplayCollectionFactory $displayCollectionFactory,
        private readonly AdminNotificationDisplayFactory $displayFactory,
        private readonly DisplayResource $displayResource,
        private readonly BackendAuthSession $authSession,
        private readonly ResourceConnection $resource,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Pop-up modal candidates — display_as_popup=1, not dismissed by this
     * admin, shown fewer than MAX_POPUP_SHOWS times. Newest-first.
     *
     * Wrapped in a top-level try/catch: when the publisher is unreachable
     * the local table is empty (no problem), but if the local table is
     * missing entirely (setup:upgrade hasn't run yet, DB outage, etc.)
     * we MUST NOT throw — the admin notifications block runs on every
     * admin page and propagating an exception here would 500 the entire
     * backend. Return [] instead so the page renders without notifications.
     *
     * @return AdminNotification[]
     */
    public function getPopupMessages(): array
    {
        try {
            $adminUserId = $this->getCurrentAdminUserId();
            if ($adminUserId === 0) {
                return [];
            }

            $dismissedIds = $this->loadDismissedMessageIds($adminUserId);
            $shownCounts = $this->loadShownCounts($adminUserId);

            $collection = $this->notificationCollectionFactory->create()
                ->addFieldToFilter('display_as_popup', 1)
                ->setOrder('date_added', 'DESC')
                ->setOrder('entity_id', 'DESC');

            $messages = [];
            foreach ($collection as $message) {
                $messageId = (string) $message->getData('message_id');
                if (in_array($messageId, $dismissedIds, true)) {
                    continue;
                }
                $shown = $shownCounts[$messageId] ?? 0;
                if ($shown >= self::MAX_POPUP_SHOWS) {
                    continue;
                }
                $messages[] = $message;
            }
            return $messages;
        } catch (\Throwable $e) {
            $this->logger->info('[panth_core] getPopupMessages failed (degraded gracefully)', [
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Top-bar banner candidates — pin_to_top=1, not dismissed by this
     * admin. The bar is "always visible until dismissed", so there's no
     * shown_count cap.
     *
     * Same defensive contract as getPopupMessages — exceptions are
     * logged at info level and silently turn into an empty list so the
     * admin page can keep rendering.
     *
     * @return AdminNotification[]
     */
    public function getBannerMessages(): array
    {
        try {
            $adminUserId = $this->getCurrentAdminUserId();
            if ($adminUserId === 0) {
                return [];
            }

            $dismissedIds = $this->loadDismissedMessageIds($adminUserId);
            $collection = $this->notificationCollectionFactory->create()
                ->addFieldToFilter('pin_to_top', 1)
                ->setOrder('date_added', 'DESC');

            $messages = [];
            foreach ($collection as $message) {
                $messageId = (string) $message->getData('message_id');
                if (in_array($messageId, $dismissedIds, true)) {
                    continue;
                }
                $messages[] = $message;
            }
            return $messages;
        } catch (\Throwable $e) {
            $this->logger->info('[panth_core] getBannerMessages failed (degraded gracefully)', [
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Increment the shown-count for one (admin, message) pair. Idempotent
     * upsert — first call inserts the row, subsequent calls bump the
     * counter and update last_shown_at.
     *
     * Uses raw `INSERT ... ON DUPLICATE KEY UPDATE` because Magento's
     * `insertOnDuplicate()` API misquotes mixed expression/literal update
     * clauses — passing `['col' => $literal]` ends up generating
     * `col = VALUES(<literal>)`, which MySQL interprets as a column-name
     * lookup and 1054-errors. Using raw SQL with named expressions is
     * unambiguous and produces a single round-trip.
     */
    public function markShown(string $messageId): void
    {
        $adminUserId = $this->getCurrentAdminUserId();
        if ($adminUserId === 0 || $messageId === '') {
            return;
        }
        try {
            $connection = $this->resource->getConnection();
            $table = $connection->quoteIdentifier(
                $this->resource->getTableName('panth_core_notification_display')
            );
            $now = gmdate('Y-m-d H:i:s');
            $connection->query(
                "INSERT INTO $table (admin_user_id, message_id, shown_count, last_shown_at)
                 VALUES (?, ?, 1, ?)
                 ON DUPLICATE KEY UPDATE
                     shown_count = shown_count + 1,
                     last_shown_at = VALUES(last_shown_at)",
                [$adminUserId, $messageId, $now]
            );
        } catch (\Throwable $e) {
            $this->logger->info('[panth_core] markShown failed', [
                'admin_user_id' => $adminUserId,
                'message_id'    => $messageId,
                'error'         => $e->getMessage(),
            ]);
        }
    }

    /**
     * Mark a message as dismissed by the current admin user. Idempotent —
     * second call simply refreshes the timestamp.
     */
    public function dismiss(string $messageId): void
    {
        $adminUserId = $this->getCurrentAdminUserId();
        if ($adminUserId === 0 || $messageId === '') {
            return;
        }
        try {
            $connection = $this->resource->getConnection();
            $table = $connection->quoteIdentifier(
                $this->resource->getTableName('panth_core_notification_display')
            );
            $now = gmdate('Y-m-d H:i:s');
            $connection->query(
                "INSERT INTO $table (admin_user_id, message_id, dismissed_at)
                 VALUES (?, ?, ?)
                 ON DUPLICATE KEY UPDATE
                     dismissed_at = VALUES(dismissed_at)",
                [$adminUserId, $messageId, $now]
            );
        } catch (\Throwable $e) {
            $this->logger->info('[panth_core] dismiss failed', [
                'admin_user_id' => $adminUserId,
                'message_id'    => $messageId,
                'error'         => $e->getMessage(),
            ]);
        }
    }

    private function getCurrentAdminUserId(): int
    {
        try {
            $user = $this->authSession->getUser();
            return $user ? (int) $user->getId() : 0;
        } catch (\Throwable) {
            return 0;
        }
    }

    /**
     * @return string[]
     */
    private function loadDismissedMessageIds(int $adminUserId): array
    {
        try {
            $collection = $this->displayCollectionFactory->create()
                ->addFieldToFilter('admin_user_id', $adminUserId)
                ->addFieldToFilter('dismissed_at', ['notnull' => true]);
            $ids = [];
            foreach ($collection as $row) {
                $ids[] = (string) $row->getData('message_id');
            }
            return $ids;
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * @return array<string, int> message_id => shown_count
     */
    private function loadShownCounts(int $adminUserId): array
    {
        try {
            $collection = $this->displayCollectionFactory->create()
                ->addFieldToFilter('admin_user_id', $adminUserId);
            $counts = [];
            foreach ($collection as $row) {
                $counts[(string) $row->getData('message_id')] = (int) $row->getData('shown_count');
            }
            return $counts;
        } catch (\Throwable) {
            return [];
        }
    }
}
