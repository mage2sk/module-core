<?php
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

    public function isEnabled($storeId = null): bool
    {
        return (bool) $this->scopeConfig->getValue(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function isDebugEnabled($storeId = null): bool
    {
        return (bool) $this->scopeConfig->getValue(
            self::XML_PATH_DEBUG,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function isCacheEnabled($storeId = null): bool
    {
        return (bool) $this->scopeConfig->getValue(
            self::XML_PATH_CACHE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function log(string $message, array $context = [])
    {
        if ($this->isDebugEnabled()) {
            $this->_logger->info('Panth Core: ' . $message, $context);
        }
    }
}
