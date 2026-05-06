<?php
/**
 * Copyright © Panth Infotech. All rights reserved.
 */
declare(strict_types=1);

namespace Panth\Core\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class AdminNotification extends AbstractDb
{
    protected function _construct(): void
    {
        $this->_init('panth_core_notification', 'entity_id');
    }
}
