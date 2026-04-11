<?php
/**
 * Admin Access Control Plugin (Disabled)
 * License validation removed - all sections are visible
 *
 * @category  Panth
 * @package   Panth_Core
 */
declare(strict_types=1);

namespace Panth\Core\Plugin;

use Magento\Config\Model\Config\Structure\Element\Section;

class AdminAccessControl
{
    /**
     * Always allow visibility - license check removed
     *
     * @param Section $subject
     * @param bool $result
     * @return bool
     */
    public function afterIsVisible(Section $subject, $result)
    {
        return $result;
    }
}
