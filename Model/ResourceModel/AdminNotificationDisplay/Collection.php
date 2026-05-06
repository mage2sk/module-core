<?php
/**
 * Copyright © Panth Infotech. All rights reserved.
 */
declare(strict_types=1);

namespace Panth\Core\Model\ResourceModel\AdminNotificationDisplay;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Panth\Core\Model\AdminNotificationDisplay as Model;
use Panth\Core\Model\ResourceModel\AdminNotificationDisplay as ResourceModel;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'entity_id';

    protected function _construct(): void
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
