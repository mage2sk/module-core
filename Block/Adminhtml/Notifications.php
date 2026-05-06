<?php
/**
 * Copyright © Panth Infotech. All rights reserved.
 */
declare(strict_types=1);

namespace Panth\Core\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\FormKey as FormKeyService;
use Panth\Core\Service\AdminNotificationProvider;

/**
 * Renders the popup-on-login modal and pin-to-top banner on every admin
 * page. Pulls candidate messages from AdminNotificationProvider and
 * exposes a small JSON payload + a couple of POST endpoints to the
 * template — no inline duplication of message text in JS, no XSS risk
 * from publisher-supplied content.
 */
class Notifications extends Template
{
    protected $_template = 'Panth_Core::notifications.phtml';

    private FormKeyService $panthFormKey;
    private AdminNotificationProvider $provider;

    /**
     * Note: parent `Magento\Backend\Block\Template` already defines a
     * `$formKey` property (non-readonly) — promoting another `$formKey`
     * here would conflict. We hold our own form-key service under a
     * distinct name to keep the parent untouched.
     */
    public function __construct(
        Context $context,
        AdminNotificationProvider $provider,
        FormKeyService $formKey,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->provider = $provider;
        $this->panthFormKey = $formKey;
    }

    /**
     * @return \Panth\Core\Model\AdminNotification[]
     */
    public function getPopupMessages(): array
    {
        return $this->provider->getPopupMessages();
    }

    /**
     * @return \Panth\Core\Model\AdminNotification[]
     */
    public function getBannerMessages(): array
    {
        return $this->provider->getBannerMessages();
    }

    public function getMarkShownUrl(): string
    {
        return $this->getUrl('panthcore/notifications/markShown');
    }

    public function getDismissUrl(): string
    {
        return $this->getUrl('panthcore/notifications/dismiss');
    }

    public function getFormKey(): string
    {
        return (string) $this->panthFormKey->getFormKey();
    }
}
