<?php
/**
 * Base Module License Validator Helper (Disabled)
 * License validation removed - all checks return true
 *
 * @category  Panth
 * @package   Panth_Core
 */
declare(strict_types=1);

namespace Panth\Core\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Panth\Core\Service\LicenseValidator;
use Panth\Core\Service\WebhookNotifier;
use Psr\Log\LoggerInterface;

abstract class ModuleLicenseValidator extends AbstractHelper
{
    /**
     * @var LicenseValidator
     */
    protected $licenseValidator;

    /**
     * @var WebhookNotifier
     */
    protected $webhookNotifier;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param Context $context
     * @param LicenseValidator $licenseValidator
     * @param WebhookNotifier $webhookNotifier
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        LicenseValidator $licenseValidator,
        WebhookNotifier $webhookNotifier,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->licenseValidator = $licenseValidator;
        $this->webhookNotifier = $webhookNotifier;
        $this->logger = $logger;
    }

    /**
     * Validate module license - always returns true
     *
     * @param bool $forceCheck
     * @return bool
     */
    public function validateLicense($forceCheck = false)
    {
        return true;
    }

    /**
     * Check if module is enabled (config check only, no license check)
     *
     * @return bool
     */
    public function isModuleEnabled()
    {
        $enabled = $this->scopeConfig->getValue(
            $this->getConfigPath() . '/general/enabled',
            ScopeInterface::SCOPE_STORE
        );

        return (bool)$enabled;
    }

    /**
     * Validate and notify - always returns true
     *
     * @param string $operation
     * @return bool
     */
    public function validateAndNotify($operation = 'unknown')
    {
        return true;
    }

    /**
     * Get module name
     *
     * @return string
     */
    abstract protected function getModuleName();

    /**
     * Get config path
     *
     * @return string
     */
    abstract protected function getConfigPath();
}
