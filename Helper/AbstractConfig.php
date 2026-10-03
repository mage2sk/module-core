<?php
declare(strict_types=1);

namespace Panth\Core\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

abstract class AbstractConfig extends AbstractHelper
{
    protected function getConfig(string $path, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    abstract protected function getConfigValue(string $group, string $field, $storeId = null);

    protected function isSetFlag(string $path, $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            $path,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    abstract public function isEnabled($storeId = null): bool;
}
