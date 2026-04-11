<?php
/**
 * Panth Core - Abstract Configuration Helper
 *
 * Base helper class providing common configuration methods for all Panth modules.
 * Extend this class in your module's Helper\Data class to get standard config functionality.
 *
 * @category  Panth
 * @package   Panth_Core
 * @author    Panth Infotech
 */
declare(strict_types=1);

namespace Panth\Core\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

abstract class AbstractConfig extends AbstractHelper
{
    /**
     * Get configuration value
     *
     * Override this method in child class to set your module's XML_PATH prefix
     *
     * @param string $path Configuration path (relative to module's XML_PATH)
     * @param int|null $storeId Store ID
     * @return mixed
     */
    protected function getConfig(string $path, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get configuration value with group/field pattern
     *
     * @param string $group Configuration group
     * @param string $field Configuration field
     * @param int|null $storeId Store ID
     * @return mixed
     */
    abstract protected function getConfigValue(string $group, string $field, $storeId = null);

    /**
     * Check if configuration flag is set
     *
     * @param string $path Configuration path
     * @param int|null $storeId Store ID
     * @return bool
     */
    protected function isSetFlag(string $path, $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            $path,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if module is enabled
     * Override this in child class with your module's enable path
     *
     * @param int|null $storeId Store ID
     * @return bool
     */
    abstract public function isEnabled($storeId = null): bool;
}
