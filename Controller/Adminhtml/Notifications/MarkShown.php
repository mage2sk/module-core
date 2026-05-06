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
 * POST /panthcore/notifications/markShown — admin AJAX endpoint that
 * increments shown_count for the (admin_user, message_id) pair, so
 * popups self-extinguish after MAX_POPUP_SHOWS shows.
 */
class MarkShown extends Action implements HttpPostActionInterface
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
            $this->provider->markShown($messageId);
        }
        return $this->jsonFactory->create()->setData(['ok' => true]);
    }
}
