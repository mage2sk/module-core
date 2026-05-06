<?php
/**
 * Panth Core Helper
 * Main helper for core module functionality
 */
declare(strict_types=1);

namespace Panth\Core\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    const XML_PATH_ENABLED = 'panth_core/general/enabled';
    const XML_PATH_DEBUG = 'panth_core/general/debug_mode';
    const XML_PATH_CACHE = 'panth_core/general/cache_enabled';

    /**
     * Check if Panth Core is enabled
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isEnabled($storeId = null): bool
    {
        return (bool) $this->scopeConfig->getValue(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if debug mode is enabled
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isDebugEnabled($storeId = null): bool
    {
        return (bool) $this->scopeConfig->getValue(
            self::XML_PATH_DEBUG,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if cache is enabled
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isCacheEnabled($storeId = null): bool
    {
        return (bool) $this->scopeConfig->getValue(
            self::XML_PATH_CACHE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Log debug message if debug mode is enabled
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function log(string $message, array $context = [])
    {
        if ($this->isDebugEnabled()) {
            $this->_logger->info('Panth Core: ' . $message, $context);
        }
    }

}
