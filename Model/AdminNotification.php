<?php
/**
 * Copyright © Panth Infotech. All rights reserved.
 */
declare(strict_types=1);

namespace Panth\Core\Model;

use Magento\Framework\Model\AbstractModel;
use Panth\Core\Model\ResourceModel\AdminNotification as AdminNotificationResource;

/**
 * Local mirror of one notification message fetched from the publisher
 * feed. Carries the rich fields (popup flag, body_html, image_url, CTAs,
 * tags) that don't fit the native Magento admin inbox.
 */
class AdminNotification extends AbstractModel
{
    public const SEVERITY_CRITICAL = 'critical';
    public const SEVERITY_MAJOR = 'major';
    public const SEVERITY_MINOR = 'minor';
    public const SEVERITY_NOTICE = 'notice';

    protected $_idFieldName = 'entity_id';

    protected function _construct(): void
    {
        $this->_init(AdminNotificationResource::class);
    }
}
