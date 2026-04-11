<?php
/**
 * Panth Core - Configuration ViewModel
 *
 * Base ViewModel for accessing configuration values in templates.
 * Provides a clean interface for Hyvä templates.
 *
 * @category  Panth
 * @package   Panth_Core
 * @author    Panth Infotech
 */
declare(strict_types=1);

namespace Panth\Core\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config implements ArgumentInterface
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get configuration value
     *
     * @param string $path Configuration path
     * @param int|null $storeId Store ID
     * @return mixed
     */
    public function getConfig(string $path, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if configuration flag is set
     *
     * @param string $path Configuration path
     * @param int|null $storeId Store ID
     * @return bool
     */
    public function isSetFlag(string $path, $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            $path,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
