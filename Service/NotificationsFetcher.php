<?php
/**
 * Copyright © Panth Infotech. All rights reserved.
 */
declare(strict_types=1);

namespace Panth\Core\Service;

use Magento\AdminNotification\Model\InboxFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\Notification\MessageInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\StoreManagerInterface;
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

    /**
     * HTTP Basic credentials for the publisher endpoint. The publisher
     * module on kishansavaliya.com gates both the JSON feed AND the
     * click-log POST endpoint with these. The pair is intentionally
     * hardcoded because every consuming site needs the same shared
     * secret — there's nothing for an individual merchant to configure.
     *
     * Public so `Panth\Core\Service\ClickReporter` can reuse them for
     * the click-log POST without duplicating the literals.
     */
    public const FEED_AUTH_USER_PUBLIC = 'Kishan';
    public const FEED_AUTH_PASS_PUBLIC = 'kishan123#';

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
        private readonly StoreManagerInterface $storeManager,
        private readonly ResourceConnection $resource,
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

        $inboxRows = [];
        $skipped = 0;
        $clickProxyBase = $this->resolveClickProxyBase();
        foreach (array_slice($payload['messages'], 0, self::MAX_MESSAGES_PER_FETCH) as $msg) {
            if (!is_array($msg)) {
                $skipped++;
                continue;
            }
            if (!$this->messageApplies($msg)) {
                $skipped++;
                continue;
            }
            $row = $this->buildInboxRow($msg, $clickProxyBase);
            if ($row === null) {
                $skipped++;
                continue;
            }
            $inboxRows[] = $row;

            // Mirror the message to our local table so popup / banner
            // rendering has access to the rich metadata (image, body_html,
            // CTAs, flags) that doesn't fit Magento's native inbox schema.
            $this->upsertLocalRow($msg, $row);
        }

        if (empty($inboxRows)) {
            return ['status' => 'ok', 'fetched' => 0, 'skipped' => $skipped];
        }

        try {
            $this->inboxFactory->create()->parse($inboxRows);
        } catch (\Throwable $e) {
            $this->logger->error('[panth_core] inbox parse failed', ['error' => $e->getMessage()]);
            return ['status' => 'error', 'error' => 'inbox_parse_failed'];
        }
        return ['status' => 'ok', 'fetched' => count($inboxRows), 'skipped' => $skipped];
    }

    /**
     * Insert-or-update one row in panth_core_notification using the rich
     * publisher fields that don't fit the Magento inbox schema.
     *
     * @param array<string,mixed> $msg Original feed message
     * @param array{severity:int,date_added:string,title:string,description:string,url:string} $inboxRow
     */
    private function upsertLocalRow(array $msg, array $inboxRow): void
    {
        $messageId = trim((string) ($msg['id'] ?? ''));
        if ($messageId === '') {
            return;
        }

        $rawUrl = trim((string) ($msg['url'] ?? ''));
        if ($rawUrl !== '' && !preg_match('#^https?://#i', $rawUrl)) {
            $rawUrl = '';
        }

        $secondary = $msg['secondary_cta'] ?? null;
        $secondaryUrl = '';
        $secondaryLabel = '';
        if (is_array($secondary)) {
            $secondaryUrl = trim((string) ($secondary['url'] ?? ''));
            $secondaryLabel = trim((string) ($secondary['label'] ?? ''));
            if ($secondaryUrl !== '' && !preg_match('#^https?://#i', $secondaryUrl)) {
                $secondaryUrl = '';
                $secondaryLabel = '';
            }
        }

        $tags = $msg['tags'] ?? null;
        if (is_array($tags)) {
            $tags = implode(',', array_map(static fn ($t) => trim((string) $t), $tags));
        } else {
            $tags = (string) ($tags ?? '');
        }
        $tags = mb_substr(trim($tags), 0, 255);

        $imageUrl = trim((string) ($msg['image_url'] ?? ''));
        if ($imageUrl !== '' && !preg_match('#^https?://#i', $imageUrl)) {
            $imageUrl = '';
        }

        $severityKey = strtolower((string) ($msg['severity'] ?? 'notice'));
        if (!array_key_exists($severityKey, self::SEVERITY_MAP)) {
            $severityKey = 'notice';
        }

        $now = gmdate('Y-m-d H:i:s');
        $data = [
            'message_id'          => $messageId,
            'severity'            => $severityKey,
            'title'               => $inboxRow['title'],
            'description'         => $inboxRow['description'],
            'body_html'           => $msg['body_html'] !== null && $msg['body_html'] !== '' ? (string) $msg['body_html'] : null,
            'raw_url'             => $rawUrl !== '' ? $rawUrl : null,
            'proxy_url'           => $inboxRow['url'] !== '' ? $inboxRow['url'] : null,
            'image_url'           => $imageUrl !== '' ? $imageUrl : null,
            'cta_label'           => $msg['cta_label'] ?? null
                ? mb_substr(trim((string) $msg['cta_label']), 0, 64)
                : null,
            'secondary_cta_url'   => $secondaryUrl !== '' ? $secondaryUrl : null,
            'secondary_cta_label' => $secondaryLabel !== '' ? mb_substr($secondaryLabel, 0, 64) : null,
            'display_as_popup'    => !empty($msg['popup']) ? 1 : 0,
            'pin_to_top'          => !empty($msg['pinned']) ? 1 : 0,
            'tags'                => $tags !== '' ? $tags : null,
            'date_added'          => $inboxRow['date_added'],
            'updated_at'          => $now,
        ];

        try {
            $connection = $this->resource->getConnection();
            $tableName = $this->resource->getTableName('panth_core_notification');
            $connection->insertOnDuplicate(
                $tableName,
                $data,
                array_keys(array_diff_key($data, ['message_id' => true, 'fetched_at' => true]))
            );
        } catch (\Throwable $e) {
            $this->logger->info('[panth_core] local notification upsert failed', [
                'message_id' => $messageId,
                'error'      => $e->getMessage(),
            ]);
        }
    }

    private function downloadFeed(string $url): ?array
    {
        try {
            $this->http->setOption(CURLOPT_TIMEOUT, self::HTTP_TIMEOUT_SECONDS);
            $this->http->setOption(CURLOPT_CONNECTTIMEOUT, 5);
            $this->http->setOption(CURLOPT_SSL_VERIFYPEER, true);
            $this->http->setOption(CURLOPT_SSL_VERIFYHOST, 2);
            $this->http->setOption(CURLOPT_FOLLOWLOCATION, false);
            $this->http->setCredentials(self::FEED_AUTH_USER_PUBLIC, self::FEED_AUTH_PASS_PUBLIC);
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
    private function buildInboxRow(array $msg, string $clickProxyBase): ?array
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
        $messageId = trim((string) ($msg['id'] ?? ''));
        if ($url !== '' && preg_match('#^https?://#i', $url) && $messageId !== '' && $clickProxyBase !== '') {
            // Wrap in the consumer's click-tracking proxy so the publisher
            // sees who clicked from where. Falls back to the raw URL only
            // when message id or proxy base is unavailable — analytics is
            // best-effort, never block delivering the announcement.
            $url = $this->wrapClickProxy($clickProxyBase, $messageId, $url);
        } elseif ($url !== '' && !preg_match('#^https?://#i', $url)) {
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

    /**
     * Resolve `https://merchant.example.com/panth_core/click/index` once per
     * fetch, then reuse for every message in the batch. Returns '' when the
     * store base URL can't be resolved (rare, but in that case we fall back
     * to leaving the raw publisher URL in the inbox so admins can still
     * click through — without click tracking).
     */
    private function resolveClickProxyBase(): string
    {
        try {
            $base = rtrim((string) $this->storeManager->getStore()->getBaseUrl(), '/');
        } catch (\Throwable) {
            return '';
        }
        return $base === '' ? '' : $base . '/panth_core/click/index';
    }

    private function wrapClickProxy(string $proxyBase, string $messageId, string $destination): string
    {
        $encoded = rtrim(strtr(base64_encode($destination), '+/', '-_'), '=');
        $query = http_build_query([
            'msg' => $messageId,
            'to'  => $encoded,
        ]);
        return $proxyBase . '?' . $query;
    }

    /**
     * Resolve Panth_Core's version. We prefer Composer's InstalledVersions
     * (always present in Composer 2 and matches the actual deployed package
     * version) over module.xml's `setup_version` attribute, which most
     * declarative-schema modules — including Panth_Core — leave unset and
     * therefore would always read as "0.0.0", causing every feed message
     * with a `min_core_version` filter to be silently skipped.
     */
    private function getCoreVersion(): string
    {
        if (class_exists(\Composer\InstalledVersions::class)) {
            try {
                $version = \Composer\InstalledVersions::getVersion('mage2kishan/module-core');
                if (is_string($version) && $version !== '') {
                    // Composer occasionally returns dev-main or x.y.z@commitish — strip
                    // the @sha if present so version_compare() can do its job.
                    $version = explode('@', $version, 2)[0];
                    return ltrim($version, 'v');
                }
            } catch (\Throwable) {
                // fall through
            }
        }
        $info = $this->moduleList->getOne('Panth_Core');
        return is_array($info) && !empty($info['setup_version'])
            ? (string) $info['setup_version']
            : '0.0.0';
    }
}
