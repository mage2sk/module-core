<?php
declare(strict_types=1);

namespace Panth\Core\Model\Config\Backend;

use Magento\Framework\App\Config\Value;

class Enabled extends Value
{
    public function beforeSave()
    {
        return parent::beforeSave();
    }
}
