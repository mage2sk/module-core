<?php
/**
 * Copyright © Panth Infotech. All rights reserved.
 */
declare(strict_types=1);

namespace Panth\Core\Controller\Click;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Store\Model\StoreManagerInterface;
use Panth\Core\Service\ClickReporter;
use Psr\Log\LoggerInterface;

/**
 * GET /panth_core/click/index?msg=<message_id>&to=<base64url-encoded-destination>
 *
 * The bell-icon admin notification link points here. We:
 *   1. Decode the destination URL.
 *   2. Fire-and-forget POST a click event to the publisher.
 *   3. 302 redirect the admin to the real destination.
 *
 * Public route on purpose — the admin's browser is in the admin context
 * but the click-tracking redirect doesn't need session auth, and tying
 * the URL to a session secret would mean every cached inbox link breaks
 * the next time the admin's session rotates.
 */
class Index implements HttpGetActionInterface
{
    public function __construct(
        private readonly RequestInterface $request,
        private readonly RedirectFactory $redirectFactory,
        private readonly ClickReporter $clickReporter,
        private readonly StoreManagerInterface $storeManager,
        private readonly LoggerInterface $logger
    ) {
    }

    public function execute(): ResultInterface
    {
        $messageId = trim((string) $this->request->getParam('msg', ''));
        $destination = $this->decodeDestination((string) $this->request->getParam('to', ''));

        if ($messageId !== '' && $destination !== '') {
            try {
                $this->clickReporter->report($messageId, $destination);
            } catch (\Throwable $e) {
                $this->logger->info('[panth_core] click controller report exception', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $redirect = $this->redirectFactory->create();
        $redirect->setUrl($destination !== '' ? $destination : $this->getStoreFallbackUrl());
        return $redirect;
    }

    /**
     * Reverse the URL-safe Base64 encoding the fetcher applied. Reject
     * anything that doesn't decode to an http(s) URL.
     */
    private function decodeDestination(string $encoded): string
    {
        if ($encoded === '') {
            return '';
        }
        $decoded = base64_decode(strtr($encoded, '-_', '+/'), true);
        if ($decoded === false || $decoded === '') {
            return '';
        }
        if (!preg_match('#^https?://#i', $decoded)) {
            return '';
        }
        return $decoded;
    }

    private function getStoreFallbackUrl(): string
    {
        try {
            return (string) $this->storeManager->getStore()->getBaseUrl();
        } catch (\Throwable) {
            return '/';
        }
    }
}
