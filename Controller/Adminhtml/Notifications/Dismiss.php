<?php
/**
 * Copyright © Panth Infotech. All rights reserved.
 */
declare(strict_types=1);

namespace Panth\Core\Controller\Adminhtml\Notifications;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Panth\Core\Service\AdminNotificationProvider;

/**
 * POST /panthcore/notifications/dismiss — admin AJAX endpoint that
 * marks one notification as dismissed for the current admin user.
 * Subsequent page-loads will not surface it again as a popup or
 * banner.
 */
class Dismiss extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Magento_Backend::admin';

    public function __construct(
        Context $context,
        private readonly JsonFactory $jsonFactory,
        private readonly AdminNotificationProvider $provider
    ) {
        parent::__construct($context);
    }

    public function execute(): ResultInterface
    {
        $messageId = trim((string) $this->getRequest()->getParam('message_id', ''));
        if ($messageId !== '') {
            $this->provider->dismiss($messageId);
        }
        return $this->jsonFactory->create()->setData(['ok' => true]);
    }
}
