<?php
/**
 * Copyright © Panth Infotech. All rights reserved.
 */
declare(strict_types=1);

namespace Panth\Core\Model;

use Magento\Framework\Model\AbstractModel;
use Panth\Core\Model\ResourceModel\AdminNotificationDisplay as DisplayResource;

/**
 * Per-admin-user display state for one notification message — how many
 * times it's been shown to this admin and when it was dismissed.
 */
class AdminNotificationDisplay extends AbstractModel
{
    protected $_idFieldName = 'entity_id';

    protected function _construct(): void
    {
        $this->_init(DisplayResource::class);
    }
}
