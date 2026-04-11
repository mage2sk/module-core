<?php
/**
 * Panth Module Loader Plugin (Disabled)
 * License validation removed
 *
 * @category  Panth
 * @package   Panth_Core
 */
declare(strict_types=1);

namespace Panth\Core\Plugin;

use Magento\Framework\Module\ModuleList;

class ModuleLoaderPlugin
{
    /**
     * No-op - license validation removed
     *
     * @param ModuleList $subject
     * @param array $result
     * @return array
     */
    public function afterGetAll(ModuleList $subject, $result)
    {
        return $result;
    }
}
