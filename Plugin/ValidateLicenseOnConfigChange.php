<?php
/**
 * Plugin to validate license on config change (Disabled)
 * License validation removed
 *
 * @category  Panth
 * @package   Panth_Core
 */
declare(strict_types=1);

namespace Panth\Core\Plugin;

use Magento\Framework\App\Config\ScopeConfigInterface;

class ValidateLicenseOnConfigChange
{
    /**
     * No-op - license validation removed
     *
     * @param ScopeConfigInterface $subject
     * @param mixed $result
     * @param string $path
     * @return mixed
     */
    public function afterGetValue(ScopeConfigInterface $subject, $result, $path)
    {
        return $result;
    }
}
