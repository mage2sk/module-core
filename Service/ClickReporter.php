<?php
/**
 * Copyright © Panth Infotech. All rights reserved.
 */
declare(strict_types=1);

namespace Panth\Core\Service;

use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Fire-and-forget POST to the publisher's click-log endpoint. Called from
 * the click-proxy controller after an admin clicks a notification's URL.
 *
 * Failures are silently logged and never block the redirect — losing a
 * single analytics ping is preferable to making the admin wait while
 * kishansavaliya.com hiccups.
 */
class ClickReporter
{
    public const ENDPOINT_URL = 'https://kishansavaliya.com/panth/notifications/click';

    private const TIMEOUT_SECONDS = 3;
    private const CONNECT_TIMEOUT_SECONDS = 2;
    private const USER_AGENT_MAX_LENGTH = 200;

    public function __construct(
        private readonly Curl $http,
        private readonly StoreManagerInterface $storeManager,
        private readonly RequestInterface $request,
        private readonly ProductMetadataInterface $productMetadata,
        private readonly ModuleListInterface $moduleList,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param string $clickSource bell_inbox|popup|top_bar|other
     */
    public function report(string $messageId, string $destinationUrl, string $clickSource = 'bell_inbox'): void
    {
        if ($messageId === '' || $destinationUrl === '') {
            return;
        }
        if (!preg_match('#^https?://#i', $destinationUrl)) {
            return;
        }

        try {
            $payload = json_encode($this->buildPayload($messageId, $destinationUrl, $clickSource), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            $this->http->setOption(CURLOPT_TIMEOUT, self::TIMEOUT_SECONDS);
            $this->http->setOption(CURLOPT_CONNECTTIMEOUT, self::CONNECT_TIMEOUT_SECONDS);
            $this->http->setOption(CURLOPT_SSL_VERIFYPEER, true);
            $this->http->setOption(CURLOPT_SSL_VERIFYHOST, 2);
            $this->http->setOption(CURLOPT_FOLLOWLOCATION, false);
            $this->http->setCredentials(
                NotificationsFetcher::FEED_AUTH_USER_PUBLIC,
                NotificationsFetcher::FEED_AUTH_PASS_PUBLIC
            );
            $this->http->addHeader('Content-Type', 'application/json');
            $this->http->addHeader('Accept', 'application/json');
            $this->http->post(self::ENDPOINT_URL, $payload);
            // Don't bother inspecting the response — the publisher returns
            // 204 on success and we don't have a retry strategy anyway.
        } catch (\Throwable $e) {
            $this->logger->info('[panth_core] click report failed (non-fatal)', [
                'message_id' => $messageId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function getSiteUrl(): string
    {
        try {
            return rtrim((string) $this->storeManager->getStore()->getBaseUrl(), '/');
        } catch (\Throwable) {
            return '';
        }
    }

    /**
     * Assemble the click payload. Sends every field the publisher's
     * ClickRecorder accepts so the analytics grid can slice clicks by
     * source, Magento version, PHP version, etc. Empty fields are
     * dropped so we don't pad the wire with `"foo":""` noise.
     *
     * The `ip` field is the END USER's IP detected from the inbound
     * request — without it the publisher's REMOTE_ADDR would be the
     * consumer site's own outbound IP (server-to-server POST), which
     * is useless for analytics. The `country_code` comes from the
     * upstream CDN's geo-header when present (Cloudflare's
     * `CF-IPCountry`, AWS CloudFront's `CloudFront-Viewer-Country`,
     * Fastly's `X-Country-Code`); we don't ship a GeoIP dependency
     * just for this column.
     *
     * @return array<string, string>
     */
    private function buildPayload(string $messageId, string $destinationUrl, string $clickSource): array
    {
        $payload = [
            'message_id'         => $messageId,
            'site_url'           => $this->getSiteUrl(),
            'site_name'          => $this->getSiteName(),
            'destination'        => $destinationUrl,
            'referer_url'        => $this->getRefererUrl(),
            'click_source'       => mb_substr($clickSource !== '' ? $clickSource : 'bell_inbox', 0, 32),
            'magento_version'    => mb_substr($this->productMetadata->getVersion(), 0, 32),
            'panth_core_version' => mb_substr($this->getCoreVersion(), 0, 32),
            'php_version'        => mb_substr(PHP_VERSION, 0, 32),
            'user_agent'         => mb_substr(
                (string) ($this->request->getServer('HTTP_USER_AGENT', '') ?? ''),
                0,
                self::USER_AGENT_MAX_LENGTH
            ),
            'ip'                 => $this->getClientIp(),
            'country_code'       => $this->getCountryCode(),
            'region'             => $this->getRegion(),
            'clicked_at'         => gmdate('Y-m-d\TH:i:s\Z'),
        ];
        return array_filter($payload, static fn ($v) => $v !== '' && $v !== null);
    }

    /**
     * Best-effort end-user IP. Prefers the first hop in
     * X-Forwarded-For (set by Cloudflare / load balancers) over
     * REMOTE_ADDR (which would be the proxy itself). Falls back to
     * Cloudflare's `CF-Connecting-IP` if XFF is absent. Capped at
     * 45 chars to fit the publisher's column.
     */
    private function getClientIp(): string
    {
        $candidates = [
            (string) ($this->request->getServer('HTTP_CF_CONNECTING_IP', '') ?? ''),
            $this->firstForwardedFor((string) ($this->request->getServer('HTTP_X_FORWARDED_FOR', '') ?? '')),
            (string) ($this->request->getServer('HTTP_X_REAL_IP', '') ?? ''),
            (string) ($this->request->getServer('REMOTE_ADDR', '') ?? ''),
        ];
        foreach ($candidates as $ip) {
            $ip = trim($ip);
            if ($ip !== '' && filter_var($ip, FILTER_VALIDATE_IP) !== false) {
                return mb_substr($ip, 0, 45);
            }
        }
        return '';
    }

    private function firstForwardedFor(string $header): string
    {
        if ($header === '') {
            return '';
        }
        // X-Forwarded-For: client, proxy1, proxy2 — first entry is the
        // original client.
        $parts = explode(',', $header, 2);
        return trim($parts[0]);
    }

    /**
     * ISO 3166-1 alpha-2 country code from the upstream CDN, when one
     * is sitting in front of the consumer site. Empty otherwise — the
     * publisher's column simply stays NULL.
     */
    private function getCountryCode(): string
    {
        $headers = ['HTTP_CF_IPCOUNTRY', 'HTTP_CLOUDFRONT_VIEWER_COUNTRY', 'HTTP_X_COUNTRY_CODE'];
        foreach ($headers as $h) {
            $code = strtoupper(trim((string) ($this->request->getServer($h, '') ?? '')));
            if ($code !== '' && preg_match('/^[A-Z]{2}$/', $code)) {
                return $code;
            }
        }
        return '';
    }

    /**
     * Region/state from the same CDN family. Cloudflare puts it in
     * `CF-Region`, CloudFront in `CloudFront-Viewer-Country-Region`.
     */
    private function getRegion(): string
    {
        $headers = ['HTTP_CF_REGION', 'HTTP_CLOUDFRONT_VIEWER_COUNTRY_REGION', 'HTTP_X_REGION'];
        foreach ($headers as $h) {
            $value = trim((string) ($this->request->getServer($h, '') ?? ''));
            if ($value !== '') {
                return mb_substr($value, 0, 64);
            }
        }
        return '';
    }

    private function getSiteName(): string
    {
        try {
            return mb_substr((string) $this->storeManager->getStore()->getName(), 0, 255);
        } catch (\Throwable) {
            return '';
        }
    }

    private function getRefererUrl(): string
    {
        $ref = (string) ($this->request->getServer('HTTP_REFERER', '') ?? '');
        if ($ref !== '' && !preg_match('#^https?://#i', $ref)) {
            return '';
        }
        return mb_substr($ref, 0, 1024);
    }

    private function getCoreVersion(): string
    {
        try {
            $info = $this->moduleList->getOne('Panth_Core');
            return is_array($info) && !empty($info['setup_version'])
                ? (string) $info['setup_version']
                : '0.0.0';
        } catch (\Throwable) {
            return '0.0.0';
        }
    }
}
