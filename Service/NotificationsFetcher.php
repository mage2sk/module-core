<?php
/**
 * Copyright © Panth Infotech. All rights reserved.
 */
declare(strict_types=1);

namespace Panth\Core\Service;

use Magento\AdminNotification\Model\InboxFactory;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\Notification\MessageInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Psr\Log\LoggerInterface;

/**
 * Fetches a JSON notifications feed published by Panth Infotech and inserts
 * any new entries into Magento's native admin inbox (the bell icon in the
 * backend header). Lets us push announcements (security advisories, version
 * tag releases, breaking-change notices) to every site that has Panth_Core
 * installed without each merchant needing to subscribe to anything.
 *
 * Feed format (JSON, served over HTTPS):
 *
 *   {
 *     "version": 1,
 *     "messages": [
 *       {
 *         "id": "panth-2026-05-05-perf-php84",          // unique per message
 *         "severity": "notice",                         // critical|major|minor|notice
 *         "title": "Performance modules updated for PHP 8.4",
 *         "description": "Run composer update mage2kishan/* ...",
 *         "url": "https://kishansavaliya.com/blog/...",  // optional, https only
 *         "date_added": "2026-05-05T10:00:00Z",         // optional, ISO 8601
 *         "modules": ["mage2kishan/module-perf-debugger"], // optional filter
 *         "min_core_version": "1.0.0"                    // optional gate
 *       }
 *     ]
 *   }
 *
 * Magento_AdminNotification's `Inbox::parse()` already dedupes by hash
 * (severity+title+description+date+url), so re-fetching the same feed is
 * safe — only new messages land in the inbox.
 */
class NotificationsFetcher
{
    /**
     * Canonical Panth Infotech notifications feed. Hardcoded — the merchant
     * doesn't get to configure this because the whole point of the system
     * is that Panth Infotech can push announcements to every install
     * uniformly, not have each merchant pin a stale URL.
     */
    public const FEED_URL = 'https://kishansavaliya.com/panth/notifications.json';

    private const HTTP_TIMEOUT_SECONDS = 10;
    private const MAX_BODY_BYTES = 256 * 1024;
    private const MAX_TITLE_LENGTH = 200;
    private const MAX_DESCRIPTION_LENGTH = 2000;
    private const MAX_MESSAGES_PER_FETCH = 50;

    private const SEVERITY_MAP = [
        'critical' => MessageInterface::SEVERITY_CRITICAL,
        'major'    => MessageInterface::SEVERITY_MAJOR,
        'minor'    => MessageInterface::SEVERITY_MINOR,
        'notice'   => MessageInterface::SEVERITY_NOTICE,
    ];

    public function __construct(
        private readonly Curl $http,
        private readonly InboxFactory $inboxFactory,
        private readonly SerializerInterface $serializer,
        private readonly ModuleListInterface $moduleList,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Pull the feed and import new messages. The feature is unconditionally
     * enabled — disabling it requires disabling the whole Panth_Core module
     * (which the cron and CLI both depend on through the module sequence).
     *
     * @return array{status:string, fetched?:int, skipped?:int, error?:string}
     */
    public function fetch(): array
    {
        $payload = $this->downloadFeed(self::FEED_URL);
        if ($payload === null) {
            return ['status' => 'error', 'error' => 'fetch_failed'];
        }

        if (!isset($payload['messages']) || !is_array($payload['messages'])) {
            return ['status' => 'error', 'error' => 'malformed'];
        }

        $rows = [];
        $skipped = 0;
        foreach (array_slice($payload['messages'], 0, self::MAX_MESSAGES_PER_FETCH) as $msg) {
            if (!is_array($msg)) {
                $skipped++;
                continue;
            }
            if (!$this->messageApplies($msg)) {
                $skipped++;
                continue;
            }
            $row = $this->buildInboxRow($msg);
            if ($row === null) {
                $skipped++;
                continue;
            }
            $rows[] = $row;
        }

        if (empty($rows)) {
            return ['status' => 'ok', 'fetched' => 0, 'skipped' => $skipped];
        }

        try {
            $this->inboxFactory->create()->parse($rows);
        } catch (\Throwable $e) {
            $this->logger->error('[panth_core] inbox parse failed', ['error' => $e->getMessage()]);
            return ['status' => 'error', 'error' => 'inbox_parse_failed'];
        }
        return ['status' => 'ok', 'fetched' => count($rows), 'skipped' => $skipped];
    }

    private function downloadFeed(string $url): ?array
    {
        try {
            $this->http->setOption(CURLOPT_TIMEOUT, self::HTTP_TIMEOUT_SECONDS);
            $this->http->setOption(CURLOPT_CONNECTTIMEOUT, 5);
            $this->http->setOption(CURLOPT_SSL_VERIFYPEER, true);
            $this->http->setOption(CURLOPT_SSL_VERIFYHOST, 2);
            $this->http->setOption(CURLOPT_FOLLOWLOCATION, false);
            $this->http->addHeader('Accept', 'application/json');
            $this->http->addHeader(
                'User-Agent',
                'Panth_Core/' . $this->getCoreVersion() . ' (+notifications-fetcher)'
            );
            $this->http->get($url);

            $status = (int) $this->http->getStatus();
            if ($status !== 200) {
                $this->logger->warning('[panth_core] notifications feed non-200', [
                    'url' => $url,
                    'status' => $status,
                ]);
                return null;
            }
            $body = (string) $this->http->getBody();
            if ($body === '' || strlen($body) > self::MAX_BODY_BYTES) {
                $this->logger->warning('[panth_core] notifications feed body invalid', [
                    'bytes' => strlen($body),
                ]);
                return null;
            }
            $decoded = $this->serializer->unserialize($body);
            return is_array($decoded) ? $decoded : null;
        } catch (\Throwable $e) {
            $this->logger->error('[panth_core] notifications feed exception', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Per-message gating: optional `modules` filter (only show on sites that
     * have those modules installed) + optional `min_core_version` (skip on
     * older Panth_Core releases).
     */
    private function messageApplies(array $msg): bool
    {
        $modules = $msg['modules'] ?? null;
        if (is_array($modules) && $modules !== []) {
            $found = false;
            foreach ($modules as $compositeName) {
                if (!is_string($compositeName) || $compositeName === '') {
                    continue;
                }
                $moduleName = $this->composerToModuleName($compositeName);
                if ($moduleName !== null && $this->moduleList->has($moduleName)) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                return false;
            }
        }

        $minCoreVersion = $msg['min_core_version'] ?? null;
        if (is_string($minCoreVersion) && $minCoreVersion !== '') {
            if (version_compare($this->getCoreVersion(), $minCoreVersion, '<')) {
                return false;
            }
        }
        return true;
    }

    /**
     * Map "mage2kishan/module-foo-bar" to "Panth_FooBar" so we can match against
     * Magento's installed-module list (which is keyed by module name, not
     * composer package name).
     */
    private function composerToModuleName(string $composite): ?string
    {
        $parts = explode('/', $composite, 2);
        if (count($parts) !== 2) {
            return null;
        }
        $tail = $parts[1];
        if (str_starts_with($tail, 'module-')) {
            $tail = substr($tail, 7);
        }
        $studly = str_replace(' ', '', ucwords(str_replace('-', ' ', $tail)));
        return $studly === '' ? null : 'Panth_' . $studly;
    }

    /**
     * @return array{severity:int,date_added:string,title:string,description:string,url:string}|null
     */
    private function buildInboxRow(array $msg): ?array
    {
        $title = trim((string) ($msg['title'] ?? ''));
        $description = trim((string) ($msg['description'] ?? ''));
        if ($title === '' || $description === '') {
            return null;
        }

        $severityKey = strtolower((string) ($msg['severity'] ?? 'notice'));
        $severity = self::SEVERITY_MAP[$severityKey] ?? MessageInterface::SEVERITY_NOTICE;

        $dateAdded = null;
        $rawDate = $msg['date_added'] ?? null;
        if (is_string($rawDate) && $rawDate !== '') {
            $ts = strtotime($rawDate);
            if ($ts !== false) {
                $dateAdded = gmdate('Y-m-d H:i:s', $ts);
            }
        }
        if ($dateAdded === null) {
            $dateAdded = gmdate('Y-m-d H:i:s');
        }

        $url = trim((string) ($msg['url'] ?? ''));
        if ($url !== '' && !preg_match('#^https?://#i', $url)) {
            $url = '';
        }

        return [
            'severity'    => $severity,
            'date_added'  => $dateAdded,
            'title'       => mb_substr($title, 0, self::MAX_TITLE_LENGTH),
            'description' => mb_substr($description, 0, self::MAX_DESCRIPTION_LENGTH),
            'url'         => $url,
        ];
    }

    private function getCoreVersion(): string
    {
        $info = $this->moduleList->getOne('Panth_Core');
        return is_array($info) && !empty($info['setup_version'])
            ? (string) $info['setup_version']
            : '0.0.0';
    }
}
