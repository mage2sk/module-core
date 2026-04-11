<?php
/**
 * Base Backend Model for All Panth Module Config Fields
 * License validation removed - saves are always allowed
 *
 * @category  Panth
 * @package   Panth_Core
 */
declare(strict_types=1);

namespace Panth\Core\Model\Config\Backend;

use Magento\Framework\App\Config\Value;

class Base extends Value
{
    /**
     * Allow saving any Panth module config without restrictions
     *
     * @return $this
     */
    public function beforeSave()
    {
        return parent::beforeSave();
    }
}
