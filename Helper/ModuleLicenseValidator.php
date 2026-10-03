<?php
declare(strict_types=1);

namespace Panth\Core\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Panth\Core\Service\LicenseValidator;
use Psr\Log\LoggerInterface;

abstract class ModuleLicenseValidator extends AbstractHelper
{
    protected $licenseValidator;

    protected $logger;

    public function __construct(
        Context $context,
        LicenseValidator $licenseValidator,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->licenseValidator = $licenseValidator;
        $this->logger = $logger;
    }

    public function validateLicense($forceCheck = false)
    {
        return true;
    }

    public function isModuleEnabled()
    {
        $enabled = $this->scopeConfig->getValue(
            $this->getConfigPath() . '/general/enabled',
            ScopeInterface::SCOPE_STORE
        );

        return (bool)$enabled;
    }

    public function validateAndNotify($operation = 'unknown')
    {
        return true;
    }

    abstract protected function getModuleName();

    abstract protected function getConfigPath();
}
