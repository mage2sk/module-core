<?php
/**
 * Backend Model for Core Module Enabled Setting
 * License validation removed - always allows enabling
 *
 * @category  Panth
 * @package   Panth_Core
 */
declare(strict_types=1);

namespace Panth\Core\Model\Config\Backend;

use Magento\Framework\App\Config\Value;

class Enabled extends Value
{
    /**
     * Allow enabling without license validation
     *
     * @return $this
     */
    public function beforeSave()
    {
        return parent::beforeSave();
    }
}
