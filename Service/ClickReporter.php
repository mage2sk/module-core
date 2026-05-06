<?php
/**
 * Copyright © Panth Infotech. All rights reserved.
 */
declare(strict_types=1);

namespace Panth\Core\Service;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\HTTP\Client\Curl;
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
        private readonly LoggerInterface $logger
    ) {
    }

    public function report(string $messageId, string $destinationUrl): void
    {
        if ($messageId === '' || $destinationUrl === '') {
            return;
        }
        if (!preg_match('#^https?://#i', $destinationUrl)) {
            return;
        }

        try {
            $payload = json_encode([
                'message_id'  => $messageId,
                'site_url'    => $this->getSiteUrl(),
                'destination' => $destinationUrl,
                'clicked_at'  => gmdate('Y-m-d\TH:i:s\Z'),
                'user_agent'  => mb_substr(
                    (string) ($this->request->getServer('HTTP_USER_AGENT', '') ?? ''),
                    0,
                    self::USER_AGENT_MAX_LENGTH
                ),
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

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
}
